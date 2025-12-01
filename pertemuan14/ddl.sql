CREATE DATABASE user_level;

USE user_level;

CREATE TABLE `user` (
'id' int(11) NOT NULL,
'nama' varchar(255) NOT NULL,
'username' varchar(255) NOT NULL,
'password' varchar(255) NOT NULL,
'level' varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `user` ADD CONSTRAINT user_pk PRIMARY KEY (id);

ALTER TABLE `user` CHANGE id int(11) AUTO_INCREMENT;

INSERT INTO `user` (nama, username, password, level) VALUES ('Admin A', 'admina', 'admina123', 'admin');
INSERT INTO `user` (nama, username, password, level) VALUES ('Test A', 'testa', 'testa123', 'pegawai');
INSERT INTO `user` (nama, username, password, level) VALUES ('Test B', 'testb', 'testb456', 'pengurus');
