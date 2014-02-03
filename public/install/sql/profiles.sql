CREATE TABLE IF NOT EXISTS `profiles` (
  `id` int(11) NOT NULL,
  `first_name` varchar(150) DEFAULT 'No name',
  `last_name` varchar(150) DEFAULT 'given',
  `location` varchar(250) DEFAULT 'Unknown',
  `dob_day` int(11) DEFAULT NULL,
  `dob_month` int(11) DEFAULT NULL,
  `dob_year` int(11) DEFAULT NULL,
  `gender` enum('MALE','FEMALE','UNSPECIFIED') DEFAULT 'UNSPECIFIED',
  `views` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  KEY `location` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;