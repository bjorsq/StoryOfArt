# Change size of headings
UPDATE `text` SET fontsize = 1.2;
UPDATE `text` SET fontsize = 1.6 WHERE text_id IN (3,46,85);
UPDATE `text` SET fontsize = 1.4 WHERE text_id IN (4,28,47,52,59,62,68,73,86);
# Change behaviour of  main headings
UPDATE `text` SET behaviour_id = 2 WHERE text_id IN (3,46,85);
# Change behaviour of sub headings
UPDATE `text` SET behaviour_id = 2 WHERE text_id IN (4,28,47,52,59,62,68,73,86);

