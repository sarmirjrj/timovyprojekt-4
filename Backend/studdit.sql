SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
/*!40101 SET NAMES utf8mb4 */;

-- Drop in dependency order
DROP TABLE IF EXISTS post_votes;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS post_files;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS group_members;
DROP TABLE IF EXISTS groups;
DROP TABLE IF EXISTS users;

-- USERS
CREATE TABLE users (
  user_id      INT(11) NOT NULL AUTO_INCREMENT,
  username     VARCHAR(50)  NOT NULL,
  legal_name   VARCHAR(120) NOT NULL,
  school_mail  VARCHAR(255) NOT NULL,
  password     VARCHAR(255) NOT NULL,
  role         ENUM('student','teacher','admin') DEFAULT 'student',
  study_year   TINYINT NULL,
  profile_pic  VARCHAR(255) DEFAULT 'default.png',
  bio          TEXT NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  UNIQUE KEY ux_users_username (username),
  UNIQUE KEY ux_users_school_mail (school_mail)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- GROUPS (subjects / year)
CREATE TABLE groups (
  group_id       INT(11) NOT NULL AUTO_INCREMENT,
  name           VARCHAR(120) NOT NULL,
  year           TINYINT NOT NULL,
  description    TEXT NULL,
  owner_user_id  INT(11) NOT NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (group_id),
  KEY idx_groups_owner (owner_user_id),
  CONSTRAINT fk_groups_owner
    FOREIGN KEY (owner_user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- GROUP MEMBERS (many-to-many)
CREATE TABLE group_members (
  group_id   INT(11) NOT NULL,
  user_id    INT(11) NOT NULL,
  role       ENUM('owner','member') NOT NULL DEFAULT 'member',
  joined_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (group_id, user_id),
  KEY idx_group_members_user (user_id),
  CONSTRAINT fk_group_members_group
    FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE CASCADE,
  CONSTRAINT fk_group_members_user
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- POSTS
CREATE TABLE posts (
  post_id     INT(11) NOT NULL AUTO_INCREMENT,
  user_id     INT(11) NOT NULL,
  group_id    INT(11) NULL,
  title       VARCHAR(255) NOT NULL,
  content     TEXT NOT NULL,
  likes       INT(11) DEFAULT 0,
  dislikes    INT(11) DEFAULT 0,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (post_id),
  KEY idx_posts_user (user_id),
  KEY idx_posts_group (group_id),
  KEY idx_posts_title (title),
  CONSTRAINT fk_posts_user
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_posts_group
    FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- POST FILES (attachments)
CREATE TABLE post_files (
  file_id     INT(11) NOT NULL AUTO_INCREMENT,
  post_id     INT(11) NOT NULL,
  file_name   VARCHAR(255) NOT NULL,
  file_path   VARCHAR(255) NOT NULL,
  file_type   VARCHAR(100) DEFAULT NULL,
  uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (file_id),
  KEY idx_post_files_post (post_id),
  CONSTRAINT fk_post_files_post
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- COMMENTS
CREATE TABLE comments (
  comment_id INT(11) NOT NULL AUTO_INCREMENT,
  post_id    INT(11) NOT NULL,
  user_id    INT(11) NOT NULL,
  content    TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (comment_id),
  KEY idx_comments_post (post_id),
  KEY idx_comments_user (user_id),
  CONSTRAINT fk_comments_post
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
  CONSTRAINT fk_comments_user
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- POST VOTES (per-user vote; +1 up / -1 down)
CREATE TABLE post_votes (
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  value   TINYINT NOT NULL, -- +1 = upvote, -1 = downvote
  PRIMARY KEY (post_id, user_id),
  KEY idx_post_votes_user (user_id),
  CONSTRAINT fk_pv_post FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
  CONSTRAINT fk_pv_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
