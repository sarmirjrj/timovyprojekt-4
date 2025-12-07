from os import environ

website = "http://backend"
db_host = "database"
db_port = 3306
db_schema = environ["MARIADB_DATABASE"]
db_user = environ["MARIADB_USER"]
db_passwd = environ["MARIADB_PASSWORD"]

testing_user = {
  "username": "Testing",
  "legal_name": "Testing tester",
  "email": "testing@domain.tld",
  "year": 6,
  "role": "admin",
  "password": "UcSpVA6ZAZ*Jaycayd*a",
  "already_registered": False
}
add_testing_user = True

max_workers = 100
test_count = 500
waiting_cycles = 3

register_test = True
group_create_test = True
group_join_test = 3
posts_test = 5
file_name = "test.pdf"
vote_post_test = 10
comment_post_test = 10