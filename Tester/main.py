#!/usr/bin/env python3

import requests
import config
import random
import string
import pandas
from concurrent.futures import ThreadPoolExecutor, as_completed
from lorem_text import lorem

class WebsiteTest:
  def __init__(self, website: str, db_host: str, db_port: int, db_schema: str, db_user: str, db_passwd: str, file: str = None):    
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
    
  def crete_session(self):
    self.session = requests.Session()
  
  def register(self, timeout:int = 10) -> bool:
    if(not self.check_flag("has_login_info")):
      self.create_login(20, 10, 10, "domain.tld", 20, self.valid_roles)
    if(self.session == None):
      self.crete_session()
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
      self.crete_session()
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
      self.crete_session()
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
    self.set_flag("in_group", self.check_flag("in_group") + 1)

  def list_groups(self, timeout:int = 10) -> bool:
    if(not self.check_flag("logged_in")):
      raise RuntimeError(f"Can't list groups without being logged in")
    if(self.session == None):
      self.crete_session()
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
      self.crete_session()
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
      self.crete_session()
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

def main():
  def run_tester(tester: WebsiteTest, timeout:int = 10) -> str:
    try:
      if(config.register_test):
        tester.register(timeout)
      if(config.group_create_test):
        tester.login(timeout)
      if(config.group_create_test):
        tester.create_group(timeout)
      while(config.group_join_test > 0 and len(tester.available_groups) <= 0):
        tester.list_groups(timeout)
      for _ in range(min(config.group_join_test, len(tester.available_groups))):
        tester.join_group(timeout)
      for _ in range(config.posts_test and tester.check_flag("in_group")):
        tester.make_post(timeout)
    except Exception as err:
      print(f"{tester.username}->{tester.last_failed}: {err}")
      return f"{tester.username}->{tester.last_failed}: failed"
    else:
      print(f"{tester.username}: passed")
      return f"{tester.username}: successful"

  testers: list[WebsiteTest] = [WebsiteTest(config.website, config.db_host, config.db_port, config.db_schema, config.db_user, config.db_passwd, config.file_name) for _ in range(config.test_count)]
  max_workers = config.max_workers
  
  if(config.add_testing_user):
    test_user = WebsiteTest(config.website, config.db_host, config.db_port, config.db_schema, config.db_user, config.db_passwd)
    test_user.load_custom_login(config.testing_user)
    test_user.register()
    
  results = []
  with ThreadPoolExecutor(max_workers=max_workers) as executor:
    futures = {executor.submit(run_tester, tester): tester for tester in testers}
    for future in as_completed(futures):
      try:
        result = future.result()
      except Exception as err:
        tester = futures[future]
        result = f"{tester.username}->{tester.last_failed}: UNCAUGHT ERROR — {err}"
      print(result)
      results.append(result)
  
  with open("users.csv", 'w') as file:
    file.write("username, legal_name, email, password, year, role\n")
    for tester in testers:
      file.write(f"{tester.username}, {tester.legal_name}, {tester.email}, {tester.password}, {tester.year}, {tester.role}\n")

if __name__ == "__main__":
  main()
