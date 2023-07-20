CREATE TABLE user_activity (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11) UNSIGNED NOT NULL,
  activity_type VARCHAR(255),
  activity_details TEXT,
  activity_date DATETIME,
  post_id INT(11) UNSIGNED NOT NULL,
  comment_id INT(11) UNSIGNED NOT NULL,
  deleted_post_id INT(11) UNSIGNED,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (post_id) REFERENCES posts(id),
  FOREIGN KEY (comment_id) REFERENCES comments(id),
  FOREIGN KEY (deleted_post_id) REFERENCES posts(id) ON DELETE SET NULL
);
