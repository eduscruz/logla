CREATE TABLE mdl_logla (
    id BIGINT(10) NOT NULL auto_increment,
    course BIGINT(10) NOT NULL,
    name VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
    intro LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    introformat SMALLINT(4) NOT NULL DEFAULT 0,
    timecreated BIGINT(10) NOT NULL,
    timemodified BIGINT(10) NOT NULL DEFAULT 0,
    grade BIGINT(10) NOT NULL DEFAULT 100,
    prefeedback LONGBLOB,
    posfeedback LONGBLOB,
    idprefeedback BIGINT(10),
    idposfeedback BIGINT(10),
    idactivity BIGINT(10),
    idquiz BIGINT(10),
    prefeedbackavg NUMERIC(4,2),
    posfeedbackavg NUMERIC(4,2),
CONSTRAINT  PRIMARY KEY (id)
, KEY mdl_logl_cou2_ix (course)
)
 ENGINE = InnoDB
 DEFAULT CHARACTER SET utf8
 DEFAULT COLLATE = utf8_general_ci 
 COMMENT='Default comment for logla, please edit me';