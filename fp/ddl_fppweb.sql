CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,

    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) UNIQUE NULL,
    password VARCHAR(255) NOT NULL,

    api_token VARCHAR(128) NULL,
    
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    placeholder VARCHAR(25) NOT NULL,

    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    type ENUM('manual', 'google') NOT NULL DEFAULT 'manual',
);

CREATE TABLE job_sources (
  id INT AUTO_INCREMENT NOT NULL,
  name VARCHAR(50) UNIQUE NOT NULL,
  CONSTRAINT job_sources_pk PRIMARY KEY (id)
);

CREATE TABLE companies (
  id INT AUTO_INCREMENT NOT NULL,
  name VARCHAR(255) UNIQUE NOT NULL,
  logo TEXT NULL,
  website TEXT NULL,
  CONSTRAINT companies_pk PRIMARY KEY (id)
);

CREATE TABLE locations (
  id INT AUTO_INCREMENT NOT NULL,
  city VARCHAR(150) NOT NULL,
  state VARCHAR(150) NULL,
  country VARCHAR(50) NOT NULL,
  lat DECIMAL(10, 6) NULL,
  lon DECIMAL(10, 6) NULL,
  UNIQUE(city, state, country),
  CONSTRAINT locations_pk PRIMARY KEY (id)
);

CREATE TABLE jobs (
  id INT AUTO_INCREMENT NOT NULL,
  
  external_id VARCHAR(255) UNIQUE NOT NULL,   -- API ID
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  apply_link TEXT NOT NULL,
  is_remote BOOLEAN DEFAULT 0 NOT NULL,

  company_id INT NOT NULL,
  location_id INT NOT NULL,
  source_id INT NOT NULL,

  date_posted DATETIME NOT NULL,
  min_salary INT NULL,
  max_salary INT NULL,
  salary_period ENUM('MONTHLY', 'YEARLY') NOT NULL DEFAULT 'MONTHLY',
  raw_json JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  
  CONSTRAINT jobs_pk PRIMARY KEY (id)
);

ALTER TABLE jobs ADD COLUMN updated_at TIMESTAMP
    NULL
    DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
    AFTER created_at;

ALTER TABLE jobs MODIFY COLUMN salary_period ENUM('HOUR', 'MONTHLY', 'YEARLY') NOT NULL DEFAULT 'MONTHLY';

CREATE TABLE saved_jobs (
    id INT AUTO_INCREMENT NOT NULL,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    saved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, job_id),  -- Prevent duplicate saved jobs
    CONSTRAINT saved_jobs_pk PRIMARY KEY (id)
);

CREATE TABLE applications (
    id INT AUTO_INCREMENT NOT NULL,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    status VARCHAR(50) DEFAULT 'submitted', -- e.g. submitted, reviewed, accepted, rejected
    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, job_id),  -- Avoid duplicate applications
    CONSTRAINT applications_pk PRIMARY KEY (id)
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(255) UNIQUE NOT NULL,
    CONSTRAINT categories_pk PRIMARY KEY (id)
);

CREATE TABLE job_categories (
    job_id INT NOT NULL,
    category_id INT NOT NULL,
    CONSTRAINT job_categories_pk PRIMARY KEY (job_id, category_id)
);

ALTER TABLE jobs ADD CONSTRAINT jobs_company_fk
	FOREIGN KEY (company_id) REFERENCES companies(id)
	ON DELETE RESTRICT;

ALTER TABLE jobs ADD CONSTRAINT jobs_location_fk
	FOREIGN KEY (location_id) REFERENCES locations(id)
	ON DELETE RESTRICT;
	
ALTER TABLE jobs ADD CONSTRAINT jobs_sources_fk
	FOREIGN KEY (source_id) REFERENCES job_sources(id)
	ON DELETE RESTRICT;

CREATE INDEX idx_jobs_company ON jobs(company_id);
CREATE INDEX idx_jobs_location ON jobs(location_id);
CREATE INDEX idx_jobs_source ON jobs(source_id);
CREATE INDEX idx_jobs_date ON jobs(date_posted);

ALTER TABLE jobs ADD FULLTEXT(title, description);
      
ALTER TABLE saved_jobs ADD CONSTRAINT saved_jobs_user_fk
	FOREIGN KEY (user_id) REFERENCES users(id) 
	ON DELETE CASCADE;

ALTER TABLE saved_jobs ADD CONSTRAINT saved_jobs_job_fk
	FOREIGN KEY (job_id) REFERENCES jobs(id) 
	ON DELETE RESTRICT;
	
ALTER TABLE job_categories ADD CONSTRAINT jobcategories_job_fk
    FOREIGN KEY (job_id) REFERENCES jobs(id);
    
ALTER TABLE job_categories ADD CONSTRAINT jobcategories_category_fk
    FOREIGN KEY (category_id) REFERENCES categories(id);

ALTER TABLE applications ADD CONSTRAINT applications_user_fk
	FOREIGN KEY (user_id) REFERENCES users(id) 
	ON DELETE CASCADE;
	
ALTER TABLE applications ADD CONSTRAINT applications_job_fk
	FOREIGN KEY (job_id) REFERENCES jobs(id) 
	ON DELETE CASCADE;

CREATE INDEX idx_saved_jobs_user ON saved_jobs(user_id);
CREATE INDEX idx_saved_jobs_job ON saved_jobs(job_id);
CREATE INDEX idx_saved_jobs_user_saved_at ON saved_jobs(user_id, saved_at);

CREATE INDEX idx_job_categories_category_job
ON job_categories(category_id, job_id);

ALTER TABLE jobs ADD deleted_at TIMESTAMP NULL;

CREATE TABLE user_profiles (
    user_id INT NOT NULL,
    
    bio TEXT NULL,
    last_education VARCHAR(255) NULL,
    photo VARCHAR(1024) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT user_profiles_pk PRIMARY KEY (user_id)
);

ALTER TABLE user_profiles ADD CONSTRAINT user_profiles_user_fk
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

CREATE TABLE user_job_history (
    id INT AUTO_INCREMENT NOT NULL,
    user_id INT NOT NULL,

    job_title VARCHAR(255) NOT NULL,
    company_name VARCHAR(255) NOT NULL,

    applied_at DATETIME NULL,
    status VARCHAR(50) NULL, -- hired, rejected, in_review, etc

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT user_job_history_pk PRIMARY KEY (id)
   
);

ALTER TABLE user_job_history ADD CONSTRAINT user_job_history_user_fk
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

CREATE TABLE user_social_links (
    id INT AUTO_INCREMENT NOT NULL,
    user_id INT NOT NULL,

    platform VARCHAR(50) NOT NULL, -- github, linkedin, portfolio
    url VARCHAR(255) NOT NULL,

    CONSTRAINT user_social_links_pk PRIMARY KEY (id),
    UNIQUE (user_id, platform)
);

ALTER TABLE user_social_links ADD CONSTRAINT user_social_links_user_fk 
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
