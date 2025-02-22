
-- CREATE DATABASE updateplanner CHARACTER SET utf8 COLLATE utf8_general_ci;
-- CREATE USER 'updateplanner'@'localhost' IDENTIFIED BY 'mypassword';
-- GRANT ALL PRIVILEGES ON updateplanner.* TO 'updateplanner'@'localhost';
-- FLUSH PRIVILEGES;
-- \q

CREATE TABLE tb_user (
    id_user INT NOT NULL AUTO_INCREMENT,
    tx_login VARCHAR(20) NOT NULL,
    tx_fullname VARCHAR(100) NOT NULL,
    tx_password VARCHAR(32),
    tx_email VARCHAR(100),
    nu_page SMALLINT NOT NULL DEFAULT 10,
    tp_theme CHAR(1) NOT NULL DEFAULT 'L', /* D=Dark, L=Light */
    tp_role CHAR(1) NOT NULL DEFAULT 'G', /* A=Administrator, M=Manager, O=Operator, S=Support, G=Guest */
    fl_ldap CHAR(1) NOT NULL DEFAULT 'N',
    fl_active CHAR(1) NOT NULL DEFAULT 'Y',
    dt_record TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY pk_user (id_user),
    UNIQUE KEY uk_user (tx_login)
) ENGINE=INNODB;

CREATE TABLE tb_product (
    id_product INT NOT NULL AUTO_INCREMENT,
    tx_product VARCHAR(100) NOT NULL,
    fl_active CHAR(1) NOT NULL DEFAULT 'Y',
    dt_expire DATE,
    dt_record TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY pk_product (id_product),
    UNIQUE KEY uk_product (tx_product)
) ENGINE=INNODB;

CREATE TABLE tb_priority (
    id_priority INT NOT NULL,
    tx_priority VARCHAR(30) NOT NULL,
    fl_active CHAR(1) NOT NULL DEFAULT 'Y',
    dt_record TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY pk_priority (id_priority),
    UNIQUE KEY uk_priority (tx_priority)
) ENGINE=INNODB;

CREATE TABLE tb_recurrent (
    id_recurrent INT NOT NULL AUTO_INCREMENT,
    tx_title VARCHAR(30) NOT NULL,
    tx_report TEXT,
    fl_active CHAR(1) NOT NULL DEFAULT 'Y',
    dt_record TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY pk_recurrent (id_recurrent),
    UNIQUE KEY uk_priority (tx_title)
) ENGINE=INNODB;

CREATE TABLE tb_environment (
    id_environment INT NOT NULL AUTO_INCREMENT,
    tx_environment VARCHAR(30) NOT NULL,
    tx_color VARCHAR(6) NOT NULL,
    fl_production CHAR(1) NOT NULL,
    fl_support CHAR(1) NOT NULL,
    fl_active CHAR(1) NOT NULL DEFAULT 'Y',
    dt_record TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY pk_environment (id_environment),
    UNIQUE KEY uk_environment_name (tx_environment),
    UNIQUE KEY uk_environment_color (tx_color)
) ENGINE=INNODB;

CREATE TABLE tb_platform (
    id_platform INT NOT NULL AUTO_INCREMENT,
    tx_platform VARCHAR(20) NOT NULL,
    fl_active CHAR(1) NOT NULL DEFAULT 'Y',
    dt_record TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY pk_platform (id_platform),
    UNIQUE KEY uk_platform (tx_platform)
) ENGINE=INNODB;

CREATE TABLE tb_k8s (
    id_k8s INT NOT NULL AUTO_INCREMENT,
    tx_k8s VARCHAR(10) NOT NULL,
    fl_active CHAR(1) NOT NULL DEFAULT 'Y',
    dt_record TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY pk_k8s (id_k8s),
    UNIQUE KEY uk_k8s (tx_k8s)
) ENGINE=INNODB;

CREATE TABLE tb_cloud (
    id_cloud INT NOT NULL AUTO_INCREMENT,
    tx_cloud VARCHAR(30) NOT NULL,
    fl_active CHAR(1) NOT NULL DEFAULT 'Y',
    dt_record TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY pk_cloud (id_cloud),
    UNIQUE KEY uk_cloud (tx_cloud)
) ENGINE=INNODB;

CREATE TABLE tb_cluster (
    id_cluster BIGINT NOT NULL AUTO_INCREMENT,
    id_environment INT NOT NULL,
    id_platform INT NOT NULL,
    id_k8s INT NOT NULL,
    id_cloud INT,
    tx_cluster VARCHAR(40) NOT NULL,
    nu_node SMALLINT NOT NULL,
    fl_downstream CHAR(1) NOT NULL DEFAULT 'Y',
    fl_active CHAR(1) NOT NULL DEFAULT 'Y',
    dt_record TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY pk_cluster (id_cluster),
    UNIQUE KEY uk_cluster (id_environment, id_platform, id_k8s, id_cloud, tx_cluster),
    CONSTRAINT fk_cluster_environment FOREIGN KEY (id_environment) REFERENCES tb_environment (id_environment) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_cluster_platform FOREIGN KEY (id_platform) REFERENCES tb_platform (id_platform) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_cluster_k8s FOREIGN KEY (id_k8s) REFERENCES tb_k8s (id_k8s) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_cluster_cloud FOREIGN KEY (id_cloud) REFERENCES tb_cloud (id_cloud) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=INNODB;

CREATE TABLE tb_resource (
    id_resource BIGINT NOT NULL AUTO_INCREMENT,
    tx_resource VARCHAR(40) NOT NULL,
    tx_url VARCHAR(255),
    fl_script CHAR(1) NOT NULL DEFAULT 'Y',
    fl_active CHAR(1) NOT NULL DEFAULT 'Y',
    dt_eol DATE,
    dt_eom DATE,
    dt_record TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY pk_resource (id_resource),
    UNIQUE KEY uk_resource_name (tx_resource)
) ENGINE=INNODB;

CREATE TABLE tb_resource_version (
    id_resource_version BIGINT NOT NULL AUTO_INCREMENT,
    id_resource BIGINT NOT NULL,
    tx_version VARCHAR(40) NOT NULL,
    fl_script CHAR(1) NOT NULL DEFAULT 'Y',
    fl_active CHAR(1) NOT NULL DEFAULT 'Y',
    dt_record TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY pk_resource_version (id_resource_version),
    UNIQUE KEY uk_resource_version (id_resource, tx_version),
    CONSTRAINT fk_resource_version_resource FOREIGN KEY (id_resource) REFERENCES tb_resource (id_resource) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=INNODB;

CREATE TABLE tb_calendar (
    id_calendar BIGINT NOT NULL AUTO_INCREMENT,
    id_calendar_depends BIGINT,
    id_cluster BIGINT,
    id_environment INT,
    id_resource_version BIGINT NOT NULL,
    tx_title VARCHAR(40) NOT NULL,
    tx_description VARCHAR(255),
    fl_complete CHAR(1) NOT NULL DEFAULT 'N',
    fl_public CHAR(1) NOT NULL DEFAULT 'Y',
    dt_schedule DATETIME NOT NULL,
    dt_complete DATETIME,
    dt_record TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY pk_calendar (id_calendar),
    UNIQUE KEY uk_calendar (id_calendar_depends, id_cluster, id_environment, id_resource_version, tx_title, dt_schedule),
    CONSTRAINT fk_calendar_calendar FOREIGN KEY (id_calendar_depends) REFERENCES tb_calendar (id_calendar) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_calendar_cluster FOREIGN KEY (id_cluster) REFERENCES tb_cluster (id_cluster) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_calendar_environmen FOREIGN KEY (id_environment) REFERENCES tb_environment (id_environment) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_calendeer_resource FOREIGN KEY (id_resource_version) REFERENCES tb_resource_version (id_resource_version) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=INNODB;

CREATE TABLE tb_calendar_item (
    id_calendar_item BIGINT NOT NULL AUTO_INCREMENT,
    id_calendar BIGINT NOT NULL,
    tx_comment VARCHAR(255),
    nu_percent TINYINT,
    tp_status CHAR(1) NOT NULL DEFAULT 'N', /* N=New, W=Working, C=Completed, R=Rollback, S=Suspended */
    dt_start DATETIME NOT NULL,
    dt_end DATETIME NOT NULL,
    dt_record TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY pk_calendar_item (id_calendar_item),
    UNIQUE KEY uk_calendar_item (id_calendar, tx_comment, nu_percent, dt_start, dt_end),
    CONSTRAINT fk_calendar_item_calendar FOREIGN KEY (id_calendar) REFERENCES tb_calendar (id_calendar) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=INNODB;

CREATE TABLE tb_calendar_report (
    id_calendar_report BIGINT NOT NULL AUTO_INCREMENT,
    id_calendar BIGINT,
    tx_newtitle VARCHAR(40),
    tx_report TEXT NOT NULL,
    dt_report DATE NOT NULL,
    dt_record TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY pk_calendar_report (id_calendar_report),
    UNIQUE KEY uk_calendar_report  (id_calendar, tx_newtitle, dt_report),
    CONSTRAINT fk_calendar_report_calendar FOREIGN KEY (id_calendar) REFERENCES tb_calendar (id_calendar) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=INNODB;

CREATE TABLE tb_case (
    id_case BIGINT NOT NULL,
    id_product INT NOT NULL,
    id_priority INT NOT NULL,
    id_environment INT,
    tx_subject VARCHAR(100) NOT NULL,
    tx_description TEXT NOT NULL,
    tx_conclusion TEXT,
    tx_report TEXT,
    dt_open DATETIME NOT NULL,
    dt_close DATETIME,
    dt_record TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY pk_case (id_case),
    UNIQUE KEY uk_case (tx_subject, dt_open),
    CONSTRAINT fk_case_product FOREIGN KEY (id_product) REFERENCES tb_product (id_product) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_case_priority FOREIGN KEY (id_priority) REFERENCES tb_priority (id_priority) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_case_environmen FOREIGN KEY (id_environment) REFERENCES tb_environment (id_environment) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=INNODB;

INSERT INTO tb_priority(id_priority, tx_priority) VALUES (1, 'Critical');
INSERT INTO tb_priority(id_priority, tx_priority) VALUES (2, 'High');
INSERT INTO tb_priority(id_priority, tx_priority) VALUES (3, 'Medium');
INSERT INTO tb_priority(id_priority, tx_priority) VALUES (4, 'Low');

INSERT INTO tb_product(id_product, tx_product) VALUES (1, 'Rancher Prime');

INSERT INTO tb_environment(id_environment, tx_environment, tx_color, fl_production, fl_support) VALUES (1, 'Production', 'E35D6A', 'Y', 'Y');
INSERT INTO tb_environment(id_environment, tx_environment, tx_color, fl_production, fl_support) VALUES (2, 'Test', '3D8BFD', 'N', 'Y');

INSERT INTO tb_platform(id_platform, tx_platform) VALUES (1, 'Physical Hardware');
INSERT INTO tb_platform(id_platform, tx_platform) VALUES (2, 'VMware');
INSERT INTO tb_platform(id_platform, tx_platform) VALUES (3, 'KVM');
INSERT INTO tb_platform(id_platform, tx_platform) VALUES (4, 'XEN');
INSERT INTO tb_platform(id_platform, tx_platform) VALUES (5, 'Cloud');

INSERT INTO tb_k8s(id_k8s, tx_k8s) VALUES (1, 'RKE1');
INSERT INTO tb_k8s(id_k8s, tx_k8s) VALUES (2, 'RKE2');
INSERT INTO tb_k8s(id_k8s, tx_k8s) VALUES (3, 'K3s');
INSERT INTO tb_k8s(id_k8s, tx_k8s) VALUES (4, 'AKS');
INSERT INTO tb_k8s(id_k8s, tx_k8s) VALUES (5, 'EKS');
INSERT INTO tb_k8s(id_k8s, tx_k8s) VALUES (6, 'GKE');

INSERT INTO tb_cloud(id_cloud, tx_cloud) VALUES (1, 'AWS');
INSERT INTO tb_cloud(id_cloud, tx_cloud) VALUES (2, 'Azure');
INSERT INTO tb_cloud(id_cloud, tx_cloud) VALUES (3, 'GCP');
INSERT INTO tb_cloud(id_cloud, tx_cloud) VALUES (4, 'Digital Ocean');
INSERT INTO tb_cloud(id_cloud, tx_cloud) VALUES (5, 'Linode');

INSERT INTO tb_user(tp_role, tx_login, tx_fullname, tx_password) VALUES ('A', 'admin', 'Administrator User', '21232f297a57a5a743894a0e4a801fc3');

INSERT INTO tb_resource(tx_resource, tx_url, fl_script) VALUES('Update Planner', 'https://github.com/dobzinski/dobzinski-updateplanner/', 'N');
INSERT INTO tb_resource_version(id_resource, tx_version, fl_script) VALUES(1, 'v1.1.1', 'N');
INSERT INTO tb_calendar(id_calendar_depends, id_cluster, id_environment, id_resource_version, tx_title, fl_complete, dt_complete, dt_schedule) VALUES(null, null, null, 1, 'Install Update Planner', 'Y', NOW(), NOW());
