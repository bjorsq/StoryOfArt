# phpMyAdmin MySQL-Dump
# version 2.3.0
# http://phpwizard.net/phpMyAdmin/
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Oct 15, 2003 at 06:38 PM
# Server version: 4.00.14
# PHP Version: 4.3.1
# Database : `svg`
# --------------------------------------------------------

#
# Table structure for table `attributes`
#

DROP TABLE IF EXISTS attributes;
CREATE TABLE attributes (
  attribute_id int(11) NOT NULL auto_increment,
  behaviour_id int(11) NOT NULL default '0',
  attribute_name varchar(255) default NULL,
  edit_type varchar(255) NOT NULL default 'textline',
  default_value varchar(255) default NULL,
  listorder int(11) NOT NULL default '1',
  PRIMARY KEY  (attribute_id)
) TYPE=MyISAM;

#
# Dumping data for table `attributes`
#

INSERT INTO attributes VALUES (1, 1, 'first zoom length', 'number', '5', 2);
INSERT INTO attributes VALUES (2, 1, 'pause duration', 'number', '5', 3);
INSERT INTO attributes VALUES (3, 1, 'second zoom length', 'number', '5', 4);
INSERT INTO attributes VALUES (4, 1, 'start pause', 'number', '1', 1);
# --------------------------------------------------------

#
# Table structure for table `behaviours`
#

DROP TABLE IF EXISTS behaviours;
CREATE TABLE behaviours (
  behaviour_id int(11) NOT NULL auto_increment,
  behaviour_name varchar(255) NOT NULL default '',
  PRIMARY KEY  (behaviour_id)
) TYPE=MyISAM;

#
# Dumping data for table `behaviours`
#

INSERT INTO behaviours VALUES (1, 'zoom in | pause | zoom to foreground');
# --------------------------------------------------------

#
# Table structure for table `text`
#

DROP TABLE IF EXISTS text;
CREATE TABLE text (
  text_id int(11) NOT NULL auto_increment,
  content longtext,
  width int(11) NOT NULL default '350',
  lines tinyint(3) NOT NULL default '1',
  listorder tinyint(4) NOT NULL default '0',
  behaviour_id int(11) default NULL,
  PRIMARY KEY  (text_id)
) TYPE=MyISAM;

#
# Dumping data for table `text`
#

INSERT INTO text VALUES (29, 'Manzanelli paints young women often dressed like girls, often emaciated but fashionably dressed, a kind of dreamlike heroin chic, in the middle of a large canvas with a plain flat background.', 350, 1, 15, 1);
INSERT INTO text VALUES (28, 'Andrea Mantegna was an Italain renaissance painter.', 350, 1, 12, 1);
INSERT INTO text VALUES (18, 'She provided a number of implements with which to do this including knives and a gun.', 350, 1, 3, 1);
INSERT INTO text VALUES (17, 'Born 1950s, Abramovic first came to public attention when she invited visitors to a gallery in which she appeared naked, stating that the visitors could do anything they wanted to her and she would not resist or retaliate.', 350, 1, 2, 1);
INSERT INTO text VALUES (19, 'She narrowly avoided serious injury but her initial premise was correct: like the invisible barrier of etiquette surrounding an art work, there was a barrier of propriety and embarrassment protecting her that was only broached when the beginnings of a mob mentality formed.', 350, 1, 4, 1);
INSERT INTO text VALUES (20, 'Abramovic worked together with her partner Ulay until the early 1980s making performance \'sculptures\' and endurance works.', 350, 1, 5, 1);
INSERT INTO text VALUES (21, '\'Breathe\' involved them in a mouth to mouth embrace for as long as they could breathe using their combined exhaled breath. They braided their hair together and stayed sitting back to back for eight hours.', 350, 1, 6, 1);
INSERT INTO text VALUES (22, 'For another work they used a full size bow and arrow and stood facing each other, with Ulay holding the string and Abramovic holding the arrow at the point on the centre of the bow string from which it is released.', 350, 1, 7, 1);
INSERT INTO text VALUES (23, 'They maintained this state of equilibrium through tension until the strain of holding on meant they had to stop or let go of the tension in the bow which would have resulted in the release of the arrow which was aimed at Abramovic.', 350, 1, 8, 1);
INSERT INTO text VALUES (24, 'In a work called Doorway they stood naked either side of the entrance to the gallery in the doorway facing each other. This meant that those wishing to enter were forced to brush past them and had to choose who to face as they turned sideways to enter.', 350, 1, 9, 1);
INSERT INTO text VALUES (25, 'These partner works focused on endurance through concentration and on the tension in partnerships as it is manifested in physical mutual support. They are notable for the degree of politeness and gentleness combined with a power and implicit violence.', 350, 1, 10, 1);
INSERT INTO text VALUES (26, 'The artists described them as sculptors.', 350, 1, 11, 1);
INSERT INTO text VALUES (27, 'After the relationship with Ulay ended Abramovic continued to make endurance works but while these maintained a formal sculptural quality they acquired a ritual aspect, as in the work in which she sat in a circle of salt surrounded by poisonous snakes and a work made in an amethyst mining town in Brazil for which she made and wore clogs from blocks of amethyst.', 350, 1, 12, 1);
INSERT INTO text VALUES (30, 'Characterised by their monochrome flat backgrounds, the women usually have painfully thin bodies and large often bulging eyes.', 350, 1, 16, 1);
INSERT INTO text VALUES (31, 'Margarita Manzanelli is an Italian painter born in 1970s.', 350, 1, 15, 1);
INSERT INTO text VALUES (32, 'Robert Mapplethorp was a photographer working in New York in the 1980s and early 90s.', 350, 1, 17, 1);
INSERT INTO text VALUES (33, 'Mapplethorp became notorious for his photographs of gay men, often naked, sexually unambiguous such as Man in a Polyester Suit which shows an African-american man in the eponymous suit resting his penis on a stool, and another photograph showing a man urinating into another\'s mouth, both men dressed in leather.', 350, 1, 18, 1);
INSERT INTO text VALUES (34, 'Photographs like these led to him being attacked by the Christian right and his exhibitions denounced as pornography.', 350, 1, 19, 1);
INSERT INTO text VALUES (37, 'The fact that museums spent public money on exhibiting Mapplethorp\'s work led to a full scale row in the United States senate and other artists were dragged into the debate.', 350, 1, 20, 1);
INSERT INTO text VALUES (38, 'Senator Jesse Helms led the attack supported by The Association for the American Family and religious organisations.', 350, 1, 21, 1);
INSERT INTO text VALUES (39, 'The result was a substantial decrease in the amount of support given to artist by the National Endowment for the Arts, the United States government arts subsidy.', 350, 1, 22, 1);
INSERT INTO text VALUES (40, 'As well as a questioning of the definition of obscenity laws and a revision of the censorship laws making it easier to close an exhibition or require special conditions to be met.', 350, 1, 23, 1);
INSERT INTO text VALUES (41, 'There were many counter claims of discrimination on the grounds of sexuality.', 350, 1, 24, 1);
INSERT INTO text VALUES (42, 'Mapplethorp said of his work that he photographed images of love and trust, and that even the most \'sado masochistic\' of his images represented supreme trust, love and vulnerability on the part of the participants.', 350, 1, 25, 1);
INSERT INTO text VALUES (43, 'His defenders claimed that if his images were of heterosexual couples there would have been no such outcry.', 350, 1, 26, 1);
INSERT INTO text VALUES (44, 'The case went to court and a ruling was made that the photographs defined as obscene could only be exhibited in a specially constructed screened off area, with a clear indication to the public of the contents of the these works.', 350, 1, 27, 1);
INSERT INTO text VALUES (45, 'The case increased the public interest in the work and made Mapplethorp one of the best known photographers in the world.', 350, 1, 28, 1);
INSERT INTO text VALUES (46, 'Mapplethorp\'s themes were (like many gay artists of the period in which so many were lost to AIDS) love, sex and death.', 350, 1, 29, 1);
INSERT INTO text VALUES (47, 'He made many self portraits including the best known in which he wears a leather biker jacket and a leather cap, one as a young man with Patti Smith the singer on a fire escape, right up until his death, including one in which he is visibly frail and  holds a cane topped with a skull and crossbones emblem which owes an allegiance to Warhol\'s late self portraits.', 350, 1, 30, 1);
INSERT INTO text VALUES (48, 'Mapplethorp made a series of photographs of flowers which also represent sexuality.', 350, 1, 31, 1);
INSERT INTO text VALUES (49, 'He photographed lilies and other exotic flowers in the studio, these were sometimes reminiscent of Georgia O\'Keefe paintings usually black and white but some coloured.', 350, 1, 32, 1);
INSERT INTO text VALUES (50, 'Mapplethorp also photographed the body builder Lisa Lyon, whose physique he considered to be perfect and made commissioned portraits, for which he was much sought after by the rich and famous.', 350, 1, 33, 1);
INSERT INTO text VALUES (51, 'Robert Mapplethorp died of AIDS in 1994.', 350, 1, 34, 1);
INSERT INTO text VALUES (52, 'THE RENAISSANCE PERIOD', 350, 1, 1, 1);
# --------------------------------------------------------

#
# Table structure for table `text_seq`
#

DROP TABLE IF EXISTS text_seq;
CREATE TABLE text_seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

#
# Dumping data for table `text_seq`
#

INSERT INTO text_seq VALUES (52);
# --------------------------------------------------------

#
# Table structure for table `values`
#

DROP TABLE IF EXISTS values;
CREATE TABLE values (
  attribute_id int(11) NOT NULL default '0',
  text_id int(11) NOT NULL default '0',
  attribute_value varchar(255) NOT NULL default ''
) TYPE=MyISAM;

#
# Dumping data for table `values`
#

INSERT INTO values VALUES (3, 18, '5');
INSERT INTO values VALUES (2, 18, '5');
INSERT INTO values VALUES (1, 18, '5');
INSERT INTO values VALUES (4, 18, '1');
INSERT INTO values VALUES (3, 17, '5');
INSERT INTO values VALUES (2, 17, '5');
INSERT INTO values VALUES (1, 17, '5');
INSERT INTO values VALUES (4, 17, '2');
INSERT INTO values VALUES (4, 19, '1');
INSERT INTO values VALUES (1, 19, '5');
INSERT INTO values VALUES (2, 19, '5');
INSERT INTO values VALUES (3, 19, '5');
INSERT INTO values VALUES (4, 20, '1');
INSERT INTO values VALUES (1, 20, '5');
INSERT INTO values VALUES (2, 20, '5');
INSERT INTO values VALUES (3, 20, '5');
INSERT INTO values VALUES (4, 21, '1');
INSERT INTO values VALUES (1, 21, '5');
INSERT INTO values VALUES (2, 21, '5');
INSERT INTO values VALUES (3, 21, '5');
INSERT INTO values VALUES (4, 22, '1');
INSERT INTO values VALUES (1, 22, '5');
INSERT INTO values VALUES (2, 22, '5');
INSERT INTO values VALUES (3, 22, '5');
INSERT INTO values VALUES (4, 23, '1');
INSERT INTO values VALUES (1, 23, '5');
INSERT INTO values VALUES (2, 23, '5');
INSERT INTO values VALUES (3, 23, '5');
INSERT INTO values VALUES (4, 24, '1');
INSERT INTO values VALUES (1, 24, '5');
INSERT INTO values VALUES (2, 24, '5');
INSERT INTO values VALUES (3, 24, '5');
INSERT INTO values VALUES (4, 25, '1');
INSERT INTO values VALUES (1, 25, '5');
INSERT INTO values VALUES (2, 25, '5');
INSERT INTO values VALUES (3, 25, '5');
INSERT INTO values VALUES (4, 26, '1');
INSERT INTO values VALUES (1, 26, '5');
INSERT INTO values VALUES (2, 26, '5');
INSERT INTO values VALUES (3, 26, '5');
INSERT INTO values VALUES (4, 27, '1');
INSERT INTO values VALUES (1, 27, '5');
INSERT INTO values VALUES (2, 27, '5');
INSERT INTO values VALUES (3, 27, '5');

