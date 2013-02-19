mysql -uroot -e"load data local infile 'output' replace into table test.user2 fields terminated by '\t' escaped by '' lines terminated by '\n' (id,val)"
