CREATE DATABASE students
    DEFAULT CHARACTER SET cp1251 # database uses only cyrillic characters (cp1251)
    DEFAULT COLLATE cp1251_general_ci;  # case-insensitive string comparison (cp1251_general_ci)
    
use students;
 
CREATE TABLE students (
	forename VARCHAR(64) NOT NULL,
	surname VARCHAR(64) NOT NULL,
	email VARCHAR(64) NOT NULL UNIQUE,
	gender BOOL NOT NULL,
	group_id CHAR(5) NOT NULL,
	exam_results SMALLINT NOT NULL,
	birth_year SMALLINT NOT NULL,
	is_foreign SMALLINT NOT NULL,
	cookie CHAR(32) NOT NULL
) ENGINE MyISAM;
