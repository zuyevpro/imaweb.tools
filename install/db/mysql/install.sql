CREATE TABLE IF NOT EXISTS imaweb_tools_migrations
(
    ID INT AUTO_INCREMENT,
    NAME VARCHAR(255) NULL,
    RUN_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
    CONSTRAINT imaweb_tools_migrations_pk
        PRIMARY KEY (id)
);
