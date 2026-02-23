INSERT INTO roles (name) VALUES ('admin'), ('user');

-- Default admin user password: admin123 (change after first login)
INSERT INTO users (username, email, password_hash)
VALUES ('admin', 'admin@goldai.local', '$2y$10$bce82sHcrmd4Ar2P7APJ7OWPXNm03jArYfkRkFo8ifRU/oYJiUxue');

INSERT INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
JOIN roles r ON r.name = 'admin'
WHERE u.email = 'admin@goldai.local';
