#!/usr/bin/python
# -*- coding: utf-8 -*-
import random, json, urllib2, codecs

generateChars = u"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
sqlBoilerplate = """SET NAMES 'utf8mb4';
use students;
INSERT INTO students(forename, surname, email, gender, group_id, exam_results, birth_year, is_foreign, cookie) VALUES
"""
usersApiUrl = "http://randus.ru/api.php"
configFile = "genusers.ini"
cookieLength = 32
outputFile = "users.sql"
numUsers = 10

class User:
  def __init__(self):
    pass
    
def generateGroup():
  groups = [u"ТФ141", u"УТ601", u"РС12", u"КЕК1", u"РР6", u"ОЧ88"]
  return groups[random.randint(0, len(groups)-1)]
  
def generateCookie():
  ## creates random string of size 'cookieLength' with chars [a-zA-Z0-9] in it.
  result = list()
  for i in range(cookieLength):
    result.append(generateChars[random.randint(0, len(generateChars)-1)])
  return "".join(result)
  
def generateEmail(login):
  hosts = ["yandex.ru", "gmail.com", "yahoo.com", "mail.ru"]
  return login + "@" + hosts[random.randint(0, len(hosts)-1)]

def generateUser():
  user = User
  data = json.loads(urllib2.urlopen(usersApiUrl).read())
  user.forename = data["fname"]
  user.surname = data["lname"]
  user.email = generateEmail(data["login"])
  user.gender = random.randint(0, 1)
  user.group = generateGroup()
  user.examResults = random.randint(123, 315)
  user.birthYear = random.randint(1988, 2002)
  user.isForeign = random.randint(0, 1)
  user.cookie = generateCookie()
  # convert all str attrs into utf-8
  for i in [x for x in dir(user) if x[0] != '_' and type(getattr(user, x)) is str]:
    setattr(user, i, i.encode('utf-8'))
  data = None
  return user
  
def generateSqlStatement(u):
  return u"('{}', '{}', '{}', {}, '{}', {}, {}, {}, '{}')".format(u.forename, u.surname, u.email, u.gender, u.group, u.examResults, u.birthYear, u.isForeign, u.cookie)

if __name__ == "__main__":
  print("Generating {} users...".format(numUsers))
  random.seed();
  fi = codecs.open(outputFile, 'w', 'utf-8')
  stmts = list()
  for i in range(10):
    stmts.append(generateSqlStatement(generateUser()))
  fi.write(sqlBoilerplate)
  fi.write(",\n".join(stmts))
  fi.write(";\n")
  fi.close()
  print("Done! Open mysql and execute {} to add new users.".format(outputFile))
    

