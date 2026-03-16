
CREATE DATABASE IF NOT EXISTS form_builder;
USE form_builder;

CREATE TABLE forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_id INT,
    field_type VARCHAR(50),
    label VARCHAR(255),
    placeholder VARCHAR(255),
    required BOOLEAN,
    options TEXT,
    field_order INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_id INT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE submission_values (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT,
    field_id INT,
    value TEXT
);
