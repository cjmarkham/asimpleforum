CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(150) NOT NULL,
  `permission` int(11) NOT NULL,
  `added` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `permission` (`permission`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

INSERT INTO `groups` (`id`, `default`, `name`, `permission`, `added`) VALUES
(1, 0, 'Admin', 127, 1388489226),
(2, 0, 'Moderator', 15, 1388489226),
(3, 1, 'Guest', 3, 1388489226);