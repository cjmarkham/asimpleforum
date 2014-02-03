CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('POST','TOPIC','USER') NOT NULL,
  `typeId` int(11) NOT NULL,
  `reporter` int(11) NOT NULL,
  `reason` text NOT NULL,
  `ip` varchar(50) NOT NULL,
  `added` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;