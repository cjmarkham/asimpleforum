CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(150) NOT NULL,
  `ip` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `perm_group` int(11) NOT NULL DEFAULT '3',
  `topics` int(11) NOT NULL DEFAULT '0',
  `posts` int(11) NOT NULL DEFAULT '0',
  `regdate` int(11) NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `lastActive` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `approved` (`approved`,`lastActive`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;