CREATE TABLE IF NOT EXISTS `USER_FAVORITES` (
  `member_id`  smallint(5),
  `fav_id` smallint(5),
  CONSTRAINT pk_member_favs PRIMARY KEY (`member_id`, `fav_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
