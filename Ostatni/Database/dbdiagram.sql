CREATE TABLE users_roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role VARCHAR(255),
  created_at DATETIME,
  updated_at DATETIME,
  created_by INT,
  updated_by INT
);

CREATE TABLE workflow (
  id INT AUTO_INCREMENT PRIMARY KEY,
  state VARCHAR(255),
  created_at DATETIME,
  updated_at DATETIME,
  created_by INT,
  updated_by INT
);

CREATE TABLE issues (
  id INT AUTO_INCREMENT PRIMARY KEY,
  year INT NOT NULL,
  number INT NOT NULL,
  title VARCHAR(255),
  published_at DATE
);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255),
  password VARCHAR(255),
  password_temp VARCHAR(255),
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(255) NOT NULL,
  role_id INT,
  reset_token VARCHAR(255),
  reset_token_expires DATETIME,
  email_verified_at DATETIME,
  created_at DATETIME,
  updated_at DATETIME,
  created_by INT,
  updated_by INT,
  FOREIGN KEY (role_id) REFERENCES users_roles(id)
);

CREATE TABLE posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  body TEXT,
  user_id INT,
  state INT,
  file_path VARCHAR(255),
  topic VARCHAR(255),
  authors TEXT,
  issue_id INT,
  published_at DATETIME,
  created_at DATETIME,
  updated_at DATETIME,
  created_by INT,
  updated_by INT,
  FOREIGN KEY (state) REFERENCES workflow(id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (issue_id) REFERENCES issues(id)
);

CREATE TABLE comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  author_id INT NOT NULL,
  parent_id INT,
  type VARCHAR(50),
  visibility VARCHAR(50),
  content TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (post_id) REFERENCES posts(id),
  FOREIGN KEY (author_id) REFERENCES users(id)
);

CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type VARCHAR(50),
  message TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  read_at DATETIME,
  related_post_id INT,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (related_post_id) REFERENCES posts(id)
);

CREATE TABLE post_assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  reviewer_id INT NOT NULL,
  assigned_by INT,
  assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  due_date DATE,
  status VARCHAR(50),
  FOREIGN KEY (post_id) REFERENCES posts(id),
  FOREIGN KEY (reviewer_id) REFERENCES users(id)
);

CREATE TABLE post_reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  reviewer_id INT NOT NULL,
  score_actuality TINYINT NOT NULL,
  score_originality TINYINT NOT NULL,
  score_language TINYINT NOT NULL,
  score_expertise TINYINT NOT NULL,
  comment TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME,
  FOREIGN KEY (post_id) REFERENCES posts(id),
  FOREIGN KEY (reviewer_id) REFERENCES users(id)
);

CREATE TABLE system_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  event_type VARCHAR(50),
  level VARCHAR(20),
  message TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
