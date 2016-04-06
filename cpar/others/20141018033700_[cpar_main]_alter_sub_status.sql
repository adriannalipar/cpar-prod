/* Stage 1 */
UPDATE cpar_main SET sub_status = 11 WHERE STATUS = 1 AND sub_status = 'Draft';
UPDATE cpar_main SET sub_status = 12 WHERE STATUS = 1 AND sub_status = 'For IMS Review';
UPDATE cpar_main SET sub_status = 13 WHERE STATUS = 1 AND sub_status = 'Pushed Back';
UPDATE cpar_main SET sub_status = 19 WHERE STATUS = 1 AND sub_status = 'Closed';

/* Stage 2 */
UPDATE cpar_main SET sub_status = 21 WHERE STATUS = 2 AND sub_status = 'For Addressee Input';
UPDATE cpar_main SET sub_status = 22 WHERE STATUS = 2 AND sub_status = 'For TL Review';
UPDATE cpar_main SET sub_status = 23 WHERE STATUS = 2 AND sub_status = 'Pushed Back';