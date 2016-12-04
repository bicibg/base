
--
-- Database: `loacal`
--

-- --------------------------------------------------------

--
-- Table structure for table `migration_scripts`
--

CREATE TABLE IF NOT EXISTS `migration_scripts` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(56) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `executed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` varchar(10) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'success',
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
