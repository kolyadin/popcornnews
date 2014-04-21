Delimiter $$

CREATE TRIGGER `pn_news_editDate` BEFORE UPDATE ON `pn_news`
FOR EACH ROW BEGIN
    SET NEW.editDate = UNIX_TIMESTAMP();
END

Delimiter $$