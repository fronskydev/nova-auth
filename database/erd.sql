CREATE TABLE users (
    id INT auto_increment PRIMARY KEY
    ,username VARCHAR(100) NOT NULL
    ,email VARCHAR(255) NOT NULL
    ,full_name VARCHAR(200) NOT NULL
    ,password VARCHAR(255) NULL
    ,password_salt VARCHAR(255) NULL
    ,is_active TINYINT (1) DEFAULT 1 NULL
    ,is_admin TINYINT (1) DEFAULT 0 NULL
    ,is_social TINYINT (1) DEFAULT 0 NULL
    ,email_verified TINYINT (1) DEFAULT 0 NULL
    ,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL
    ,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
    ,CONSTRAINT email UNIQUE (email)
    ,CONSTRAINT username UNIQUE (username)
);

CREATE TABLE authentications (
    id INT auto_increment PRIMARY KEY
    ,user_id INT NULL
    ,unique_identifier VARCHAR(255) NOT NULL
    ,type enum('EMAIL_VERIFICATION', 'PASSWORD_RESET') NOT NULL
    ,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL
    ,CONSTRAINT authentications_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX user_id ON authentications (user_id);
