CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY
    ,username TEXT NOT NULL
    ,email TEXT NOT NULL
    ,full_name TEXT NOT NULL
    ,password TEXT NULL
    ,password_salt TEXT NULL
    ,is_active TINYINT(1) NOT NULL DEFAULT 1
    ,is_admin TINYINT(1) NOT NULL DEFAULT 0
    ,is_social TINYINT(1) NOT NULL DEFAULT 0
    ,email_verified TINYINT(1) NOT NULL DEFAULT 0
    ,created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ,updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE authentications (
    id INT auto_increment PRIMARY KEY
    ,user_id INT NULL
    ,unique_identifier TEXT NOT NULL
    ,type enum('EMAIL_VERIFICATION', 'PASSWORD_RESET') NOT NULL
    ,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL
    ,CONSTRAINT authentications_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX user_id ON authentications (user_id);
