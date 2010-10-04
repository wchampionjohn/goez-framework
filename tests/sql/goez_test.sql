DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT '自動編號',
  `name` varchar(20) NOT NULL COMMENT '姓名',
  `birthYear` int(10) unsigned NOT NULL default '1979' COMMENT '出生年',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT '自動編號',
  `name` varchar(20) NOT NULL COMMENT '品名',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
