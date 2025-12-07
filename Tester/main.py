#!/usr/bin/env python3

import requests
import config
import random
import string
import pandas
import mariadb
from pyquery import PyQuery
from concurrent.futures import ThreadPoolExecutor, as_completed
from lorem_text import lorem

class WebsiteTest:
  def __init__(self, id, website: str, db_host: str, db_port: int, db_schema: str, db_user: str, db_passwd: str, file: str = None):    
    self.id = id
    self.website = website
    self.db_host = db_host
    self.db_port = db_port
    self.db_schema = db_schema
    self.db_user = db_user
    self.db_passwd = db_passwd
    self.file = file
    
    self.valid_roles = ['student','teacher','admin']
    self.empowered_roles = ['teacher','admin']
    self.admin_roles = ['admin']
    
    self.available_groups = []
    self.in_groups = []
    self.posts_table = []
    self.voted_on = []
    self.commented_on = []
    
    self.session = None
    self.flags = dict()
  
  def check_flag(self, flag):
    if(flag in self.flags.keys()):
      return self.flags[flag]
    return False
  
  def set_flag(self, flag, value):
    self.flags[flag] = value
  
  def create_login(self, username_length, name_length, surname_length, email_domain, password_length, roles):
    self.username = ''.join(random.choice(string.ascii_uppercase + string.ascii_lowercase + string.digits) for _ in range(username_length))
    self.legal_name = random.choice(string.ascii_uppercase) + ''.join(random.choice(string.ascii_lowercase) for _ in range(name_length))
    self.legal_name += ' ' + random.choice(string.ascii_uppercase) + ''.join(random.choice(string.ascii_lowercase) for _ in range(surname_length))
    self.email = self.username + '@' + email_domain
    self.password = ''.join(random.choice(string.ascii_uppercase + string.ascii_lowercase + string.digits) for _ in range(password_length))
    self.year = random.randint(1, 6)
    self.role = random.choice(roles)
    self.set_flag("has_login_info", True)
    self.session = None
    
  def load_custom_login(self, login_info):
    self.username = login_info["username"]
    self.legal_name = login_info["legal_name"]
    self.email = login_info["email"]
    self.password = login_info["password"]
    self.year = login_info["year"]
    self.role = login_info["role"]
    self.set_flag("has_login_info", True)
    self.session = None
    self.set_flag("registered", login_info["already_registered"])    
    
  def create_session(self):
    self.session = requests.Session()
  
  def register(self, timeout:int = 10) -> bool:
    if(not self.check_flag("has_login_info")):
      self.create_login(20, 10, 10, "domain.tld", 20, self.valid_roles)
    if(self.session == None):
      self.create_session()
    request_params = {
      "username": self.username,
      "legal_name": self.legal_name,
      "school_mail": self.email,
      "study_year": self.year,
      "role": self.role,
      "password": self.password
    }
    try:
      return_state = self.session.post(self.website+"/register_store.php", data=request_params, timeout=timeout)
    except requests.RequestException as err:
      self.last_failed = "Request in registration"
      raise 
    if return_state.status_code != 200:
      self.last_failed = "Registration"
      raise RuntimeError(f"Registration failed ({return_state.status_code}):\n\"\"\"\n{return_state.text}\n\"\"\"")
    self.set_flag("registered", True)

  def login(self, timeout:int = 10) -> bool:
    if(not self.check_flag("registered")):
      raise RuntimeError(f"Can't login without being registered")
    if(self.session == None):
      self.create_session()
    request_params = {
      "school_mail": self.email,
      "password": self.password
    }
    try:
      return_state = self.session.post(self.website+"/login_check.php", data=request_params, timeout=timeout)
    except requests.RequestException as err:
      self.last_failed = "Request in login"
      raise 
    if return_state.status_code != 200:
      self.last_failed = "Login"
      raise RuntimeError(f"Login failed ({return_state.status_code}):\n\"\"\"\n{return_state.text}\n\"\"\"")
    self.set_flag("logged_in", True)    

  def create_group(self, timeout:int = 10) -> bool:
    if(not self.check_flag("logged_in")):
      raise RuntimeError(f"Can't create group without being logged in")
    if(self.role not in self.empowered_roles):
      return
    if(self.session == None):
      self.create_session()
    request_params = {
      "name": self.username,
      "year": self.year, 
      "description": lorem.paragraph()
    }
    try:
      return_state = self.session.post(self.website+"/group_store.php", data=request_params, timeout=timeout)
    except requests.RequestException as err:
      self.last_failed = "Request in group creation"
      raise 
    if return_state.status_code != 200:
      self.last_failed = "Group creation"
      raise RuntimeError(f"Group creation failed ({return_state.status_code}):\n\"\"\"\n{return_state.text}\n\"\"\"")
    self.set_flag("group_created", True)
    self.in_groups.append(PyQuery(return_state.content)('a.btn.btn-primary').attr("href").split('=')[1])
    self.set_flag("in_group", self.check_flag("in_group") + 1)

  def list_groups(self, timeout:int = 10) -> bool:
    if(not self.check_flag("logged_in")):
      raise RuntimeError(f"Can't list groups without being logged in")
    if(self.session == None):
      self.create_session()
    keep_trying = True
    zipped_groups = None
    tupled_groups = None
    while keep_trying:
      try:
        return_state = self.session.get(self.website+"/group_list.php", timeout=timeout)
      except requests.RequestException as err:
        self.last_failed = "Request in group listing"
        raise 
      if return_state.status_code != 200:
        self.last_failed = "Group listing"
        raise RuntimeError(f"Group listing failed ({return_state.status_code}):\n\"\"\"\n{return_state.text}\n\"\"\"")
      zipped_groups = list(pandas.read_html(return_state.content, extract_links="all")[0].to_dict()[('', None)].items())
      try:
        _, tupled_groups = zip(*zipped_groups)
      except ValueError as err:
        keep_trying = True
        continue
      else:
        keep_trying = False
        self.available_groups = [group[1].split("=")[1] for group in list(tupled_groups)]
        self.set_flag("has_groups", True)

  def join_group(self, timeout:int = 10) -> bool:
    if(not self.check_flag("logged_in")):
      raise RuntimeError(f"Can't join group without being logged in")
    groups = [group_id for group_id in self.available_groups if group_id not in self.in_groups]
    if(not self.check_flag("has_groups") or len(groups) == 0):
      self.list_groups()
    if(self.session == None):
      self.create_session()
    group = random.choice(groups)
    request_params = {
      "group_id": group,
    }
    try:
      return_state = self.session.post(self.website+"/group_join.php", data=request_params, timeout=timeout)
    except requests.RequestException as err:
      self.last_failed = "Request in group joining"
      raise 
    if return_state.status_code != 200:
      self.last_failed = "Group joining"
      raise RuntimeError(f"Group joining failed ({return_state.status_code}):\n\"\"\"\n{return_state.text}\n\"\"\"")
    self.set_flag("in_group", self.check_flag("in_group") + 1)
    self.in_groups.append(group)

  def make_post(self, timeout:int = 10) -> bool:
    if(not self.check_flag("logged_in")):
      raise RuntimeError(f"Can't join group without being logged in")
    if(len(self.in_groups) == 0):
      raise RuntimeError(f"Can't make a post withour being in a group")
    if(self.session == None):
      self.create_session()
    request_params = {
      "title": self.username,
      "content": lorem.paragraph(),
      "group_id": random.choice(self.in_groups)
    }
    files = None
    if(self.file != None):
      filename = f"{self.username}-{''.join(random.choice(string.ascii_uppercase + string.ascii_lowercase + string.digits) for _ in range(len(self.username)))}.pdf"
      files = {"file": (filename, open(self.file, 'rb'), "application/pdf")}
    try:
      return_state = self.session.post(self.website+"/post_store.php", data=request_params, files=files, timeout=timeout)
    except requests.RequestException as err:
      self.last_failed = "Request in post making"
      raise 
    if return_state.status_code != 200:
      self.last_failed = "Post making"
      raise RuntimeError(f"Post making failed ({return_state.status_code}):\n\"\"\"\n{return_state.text}\n\"\"\"")

  def list_posts(self, timeout:int = 10) -> bool:
    if(not self.check_flag("logged_in")):
      raise RuntimeError(f"Can't list groups without being logged in")
    if(self.session == None):
      self.create_session()
    while(len(self.posts_table) <= 0):
      try:
        return_state = self.session.get(self.website+"/post_view.php", timeout=timeout)
      except requests.RequestException as err:
        self.last_failed = "Request in post listing"
        raise 
      if return_state.status_code != 200:
        self.last_failed = "Post listing"
        raise RuntimeError(f"Post listing failed ({return_state.status_code}):\n\"\"\"\n{return_state.text}\n\"\"\"")
      self.posts_table = [post.attr("href").split('=')[1] for post in PyQuery(return_state.content)('div.list-group')('a.list-group-item.list-group-item-action').items()]
      self.set_flag("has_posts", True)

  def vote_post(self, timeout:int = 10) -> bool:
    if(not self.check_flag("logged_in")):
      raise RuntimeError(f"Can't vote without being logged in")
    if(len(self.posts_table) <= 0):
      raise RuntimeError(f"Can't vote without having posts")
    if(self.session == None):
      self.create_session()
    eligible_posts = [post_id for post_id in self.posts_table if post_id not in self.voted_on]
    while(len(eligible_posts) <= 0):
      self.list_posts(timeout)
      eligible_posts = [post_id for post_id in self.posts_table if post_id not in self.voted_on]
    post_id = random.choice(eligible_posts)
    request_params = {
      "post_id": post_id,
      "action": random.choice(["up", "down"])
    }
    try:
      return_state = self.session.post(self.website+"/vote.php", data=request_params, timeout=timeout)
    except requests.RequestException as err:
      self.last_failed = "Request in vote making"
      raise 
    if return_state.status_code != 200:
      self.last_failed = "Vote making"
      raise RuntimeError(f"Vote making failed ({return_state.status_code}):\n\"\"\"\n{return_state.text}\n\"\"\"")
    self.voted_on.append(post_id)

  def comment_post(self, timeout:int = 10) -> bool:
    if(not self.check_flag("logged_in")):
      raise RuntimeError(f"Can't comment without being logged in")
    if(len(self.posts_table) <= 0):
      raise RuntimeError(f"Can't comment without having posts")
    if(self.session == None):
      self.create_session()
    eligible_posts = [post_id for post_id in self.posts_table if post_id not in self.commented_on]
    while(len(eligible_posts) <= 0):
      self.list_posts(timeout)
      eligible_posts = [post_id for post_id in self.posts_table if post_id not in self.commented_on]
    post_id = random.choice(eligible_posts)
    request_params = {
      "post_id": post_id,
      "content": lorem.paragraph()
    }
    try:
      return_state = self.session.post(self.website+"/comment_store.php", data=request_params, timeout=timeout)
    except requests.RequestException as err:
      self.last_failed = "Request in comment making"
      raise 
    if return_state.status_code != 200:
      self.last_failed = "Comment making"
      raise RuntimeError(f"Comment making failed ({return_state.status_code}):\n\"\"\"\n{return_state.text}\n\"\"\"")
    self.commented_on.append(post_id)

def check_expected_against_real(testers: list[WebsiteTest]):
  def exec_query(cursor, query):
    try:
      cursor.execute(query)
      rows = cursor.fetchall()
      return rows
    except mariadb.Error as e:
      print(f"Query error: {e}")
      raise  

  try:
    db = mariadb.connect(user=config.db_user, password=config.db_passwd, host=config.db_host, database=config.db_schema)
  except mariadb.Error as e:
    print(f"Error connecting to MariaDB: {e}")
    return
  cursor = db.cursor()
  
  try:
    if(config.register_test):
      expected_result = config.test_count
      real_result = len(exec_query(cursor, f"SELECT * FROM users WHERE school_mail <> '{config.testing_user["email"]}';"))
      if(expected_result == real_result):
        print(f"Number of users ({real_result}) is correct ({expected_result})")
      else:
        print(f"Number of users ({real_result}) is not correct ({expected_result})")
    if(config.group_create_test):
      expected_result = len([tester for tester in testers if tester.role in tester.empowered_roles])
      real_result = len(exec_query(cursor, "SELECT * FROM groups;"))
      if(expected_result == real_result):
        print(f"Number of groups ({real_result}) is correct ({expected_result})")
      else:
        print(f"Number of groups ({real_result})  is not correct ({expected_result})")
    if(config.group_join_test > 0):
      expected_result = config.test_count * config.group_join_test + len([tester for tester in testers if tester.role in tester.empowered_roles]) 
      real_result = len(exec_query(cursor, "SELECT * FROM group_members;"))
      if(expected_result == real_result):
        print(f"Number of joins ({real_result}) is correct ({expected_result})")
      else:
        print(f"Number of joins ({real_result})  is not correct ({expected_result})")
    if(config.posts_test > 0):
      expected_result = config.test_count * config.posts_test
      real_result = len(exec_query(cursor, "SELECT * FROM posts;"))
      if(expected_result == real_result):
        print(f"Number of posts ({real_result}) is correct ({expected_result})")
      else:
        print(f"Number of posts ({real_result})  is not correct ({expected_result})")
    if(config.file_name):
      expected_result = config.test_count * config.posts_test
      real_result = len(exec_query(cursor, "SELECT * FROM post_files;"))
      if(expected_result == real_result):
        print(f"Number of files ({real_result}) is correct ({expected_result})")
      else:
        print(f"Number of files ({real_result})  is not correct ({expected_result})")
    if(config.vote_post_test > 0):
      expected_result = config.test_count * config.vote_post_test
      real_result = len(exec_query(cursor, "SELECT * FROM post_votes;"))
      if(expected_result == real_result):
        print(f"Number of votes ({real_result}) is correct ({expected_result})")
      else:
        print(f"Number of votes ({real_result})  is not correct ({expected_result})")
    if(config.comment_post_test > 0):
      expected_result = config.test_count * config.comment_post_test
      real_result = len(exec_query(cursor, "SELECT * FROM comments;"))
      if(expected_result == real_result):
        print(f"Number of comments ({real_result}) is correct ({expected_result})")
      else:
        print(f"Number of comments ({real_result})  is not correct ({expected_result})")
  except mariadb.Error as e:
    print(f"Query error: {e}")
  finally:
    cursor.close()
    db.close()

def main():
  def run_tester(tester: WebsiteTest, timeout:int = 10) -> str:
    try:
      if(not tester.check_flag("skip_registration") and config.register_test):
        print(f"{tester.id} - registering")
        tester.register(timeout)
        tester.set_flag("skip_registration", True)
      if(not tester.check_flag("skip_login") and config.group_create_test):
        print(f"{tester.id} - logging in")
        tester.login(timeout)
        tester.set_flag("skip_login", True)
      if(not tester.check_flag("skip_create_group") and config.group_create_test):
        print(f"{tester.id} - creating a group")
        tester.create_group(timeout)
        tester.set_flag("skip_create_group", True)
      for i in range(config.waiting_cycles):
        if(not tester.check_flag(f"skip_waiting_for_groups_{i}")):
          tester.set_flag(f"skip_waiting_for_groups_{i}", True)
          return {"status": "PAUSE", "tester": tester}
      while(config.group_join_test > 0 and len(tester.available_groups) < config.group_join_test):
        print(f"{tester.id} - looking up groups")
        tester.list_groups(timeout)
      if(not tester.check_flag("skip_joining_group") and config.group_join_test):
        for count in range(config.group_join_test):
          print(f"{tester.id} - joining group number {count}")
          tester.join_group(timeout)
        tester.set_flag("skip_joining_group", True)
      if(not tester.check_flag("skip_making_posts") and tester.check_flag("in_group")):
        for count in range(config.posts_test):
          print(f"{tester.id} - making post number {count}")
          tester.make_post(timeout)
        tester.set_flag("skip_making_posts", True)
      for i in range(config.waiting_cycles):
        if(not tester.check_flag(f"skip_waiting_for_posts_{i}")):
          tester.set_flag(f"skip_waiting_for_posts_{i}", True)
          return {"status": "PAUSE", "tester": tester}
      while(config.vote_post_test > 0 and len(tester.posts_table) < config.vote_post_test):
        print(f"{tester.id} - looking up posts")
        tester.list_posts(timeout)
      if(not tester.check_flag("skip_voting_for_posts")):
        for count in range(config.vote_post_test):
          print(f"{tester.id} - voting number {count}")
          tester.vote_post(timeout)
        tester.set_flag("skip_voting_for_posts", True)
      for i in range(config.waiting_cycles):
        if(not tester.check_flag(f"skip_waiting_for_votes_{i}") and config.vote_post_test > 0):
          tester.set_flag(f"skip_waiting_for_votes_{i}", True)
          return {"status": "PAUSE", "tester": tester}
      while(config.vote_post_test > 0 and len(tester.posts_table) <= 0):
        print(f"{tester.id} - looking up posts")
        tester.list_posts(timeout)
      if(not tester.check_flag("skip_commenting_on_posts") and config.comment_post_test > 0):
        for count in range(config.comment_post_test):
          print(f"{tester.id} - commenting number {count}")
          tester.comment_post(timeout)
        tester.set_flag("skip_commenting_on_posts", True)

    except Exception as err:
      print(f"{tester.id}->{tester.last_failed}: {err}")
      return {"status": "DONE", "result": f"{tester.id}->{tester.last_failed}: failed", "tester": tester}
    else:
      print(f"{tester.id}: passed")
      return {"status": "DONE", "result": f"{tester.id}: successful", "tester": tester}
    

  testers: list[WebsiteTest] = [WebsiteTest(id, config.website, config.db_host, config.db_port, config.db_schema, config.db_user, config.db_passwd, config.file_name) for id in range(config.test_count)]
  max_workers = config.max_workers
  
  if(config.add_testing_user):
    test_user = WebsiteTest(-1, config.website, config.db_host, config.db_port, config.db_schema, config.db_user, config.db_passwd)
    test_user.load_custom_login(config.testing_user)
    test_user.register()
    
  results = []
  with ThreadPoolExecutor(max_workers=max_workers) as executor:
    futures = {executor.submit(run_tester, tester): tester for tester in testers}
    while futures:
      for future in as_completed(list(futures.keys())):
        tester = futures.pop(future)
        try:
          result = future.result()
        except Exception as err:
          result = f"{tester.username}->{tester.last_failed}: UNCAUGHT ERROR — {err}"
          continue
        else: 
          if result["status"] == "PAUSE":
            f = executor.submit(run_tester, tester)
            futures[f] = tester
            continue
          elif result["status"] == "DONE":
            result = result["result"]

        print(result)
        results.append(result)
  
  check_expected_against_real(testers)
  
  with open("users.csv", 'w') as file:
    file.write("username, legal_name, email, password, year, role\n")
    for tester in testers:
      file.write(f"{tester.username}, {tester.legal_name}, {tester.email}, {tester.password}, {tester.year}, {tester.role}\n")

if __name__ == "__main__":
  main()
