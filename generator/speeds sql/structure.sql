# phpMyAdmin MySQL-Dump
# version 2.5.1
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Nov 22, 2003 at 11:33 PM
# Server version: 4.0.14
# PHP Version: 4.3.1
# Database : `svg`
# --------------------------------------------------------

#
# Table structure for table `attributes`
#
# Creation: Oct 14, 2003 at 09:08 AM
# Last update: Nov 19, 2003 at 01:52 PM
#

CREATE TABLE `attributes` (
  `attribute_id` int(11) NOT NULL auto_increment,
  `behaviour_id` int(11) NOT NULL default '0',
  `attribute_name` varchar(255) default NULL,
  `edit_type` varchar(255) NOT NULL default 'textline',
  `default_value` varchar(255) default NULL,
  `listorder` int(11) NOT NULL default '1',
  PRIMARY KEY  (`attribute_id`)
) TYPE=MyISAM AUTO_INCREMENT=12 ;

#
# Dumping data for table `attributes`
#

INSERT INTO `attributes` VALUES (1, 1, 'first zoom length', 'number', '15', 2);
INSERT INTO `attributes` VALUES (2, 1, 'pause duration', 'number', '0', 3);
INSERT INTO `attributes` VALUES (3, 1, 'second zoom length', 'number', '15', 4);
INSERT INTO `attributes` VALUES (4, 1, 'start pause', 'number', '0', 1);
INSERT INTO `attributes` VALUES (5, 3, 'start pause', 'number', '0.8', 1);
INSERT INTO `attributes` VALUES (6, 3, 'read pause', 'number', '3', 2);
INSERT INTO `attributes` VALUES (7, 3, 'fade duration', 'number', '0.2', 3);
INSERT INTO `attributes` VALUES (8, 2, 'start pause', 'number', '0', 1);
INSERT INTO `attributes` VALUES (9, 2, 'first zoom length', 'number', '15', 2);
INSERT INTO `attributes` VALUES (10, 2, 'pause duration', 'number', '0', 3);
INSERT INTO `attributes` VALUES (11, 2, 'second zoom length', 'number', '15', 4);
# --------------------------------------------------------

#
# Table structure for table `behaviours`
#
# Creation: Oct 10, 2003 at 10:38 PM
# Last update: Oct 23, 2003 at 09:23 PM
#

CREATE TABLE `behaviours` (
  `behaviour_id` int(11) NOT NULL auto_increment,
  `behaviour_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`behaviour_id`)
) TYPE=MyISAM AUTO_INCREMENT=4 ;

#
# Dumping data for table `behaviours`
#

INSERT INTO `behaviours` VALUES (1, 'zoom down | pause | zoom down');
INSERT INTO `behaviours` VALUES (3, 'fade in | pause | fade out');
INSERT INTO `behaviours` VALUES (2, 'zoom up | pause | zoom up');
# --------------------------------------------------------

#
# Table structure for table `section_seq`
#
# Creation: Oct 29, 2003 at 11:37 AM
# Last update: Oct 29, 2003 at 12:05 PM
#

CREATE TABLE `section_seq` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=5 ;

#
# Dumping data for table `section_seq`
#

INSERT INTO `section_seq` VALUES (4);
# --------------------------------------------------------

#
# Table structure for table `sections`
#
# Creation: Nov 22, 2003 at 11:33 PM
# Last update: Nov 22, 2003 at 11:33 PM
#

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL default '0',
  `section_name` varchar(50) NOT NULL default 'section',
  `listorder` mediumint(9) NOT NULL default '0',
  `section_start` int(11) NOT NULL default '0',
  `section_finish` int(11) NOT NULL default '0',
  `section_type` varchar(10) NOT NULL default 'subheading',
  PRIMARY KEY  (`section_id`)
) TYPE=MyISAM;

#
# Dumping data for table `sections`
#

# --------------------------------------------------------

#
# Table structure for table `text`
#
# Creation: Nov 22, 2003 at 11:33 PM
# Last update: Nov 22, 2003 at 11:33 PM
#

CREATE TABLE `text` (
  `text_id` int(11) NOT NULL auto_increment,
  `content` longtext,
  `listorder` int(11) NOT NULL default '0',
  `behaviour_id` int(11) default NULL,
  `fontsize` float NOT NULL default '1',
  `in_section` int(11) NOT NULL default '0',
  PRIMARY KEY  (`text_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `text`
#

# --------------------------------------------------------

#
# Table structure for table `text_seq`
#
# Creation: Oct 14, 2003 at 09:59 AM
# Last update: Nov 18, 2003 at 12:52 PM
#

CREATE TABLE `text_seq` (
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=4332 ;

#
# Dumping data for table `text_seq`
#

INSERT INTO `text_seq` VALUES (4331);
# --------------------------------------------------------

#
# Table structure for table `values`
#
# Creation: Nov 22, 2003 at 11:33 PM
# Last update: Nov 22, 2003 at 11:33 PM
#

CREATE TABLE `values` (
  `attribute_id` int(11) NOT NULL default '0',
  `text_id` int(11) NOT NULL default '0',
  `attribute_value` varchar(255) NOT NULL default ''
) TYPE=MyISAM;

#
# Dumping data for table `values`
#


