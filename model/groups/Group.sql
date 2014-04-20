CREATE TABLE `pn_groups` (
  `id`          INT          NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(200) NOT NULL,
  `description` TEXT,
  `createTime`  DATETIME     NOT NULL,
  `editTime`    DATETIME     NOT NULL,
  `private`     INT(1)       NOT NULL DEFAULT 0,
  `owner`       INT          NOT NULL,
  `poster`      INT          NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8;