# $Id$

ALTER TABLE %DB_TBL_PREFIX%repeat 
ADD COLUMN status    tinyint NOT NULL DEFAULT 0;
