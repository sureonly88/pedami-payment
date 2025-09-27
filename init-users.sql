-- User monitor untuk MaxScale (hak minimal yang umum dipakai)
CREATE USER IF NOT EXISTS 'maxscale_mon'@'%' IDENTIFIED BY 'cykboq-xuzcoj-hiFza1';
GRANT REPLICATION CLIENT, SHOW DATABASES, PROCESS ON *.* TO 'maxscale_mon'@'%';

-- User aplikasi (batasi ke database aplikasi)
CREATE USER IF NOT EXISTS 'appuser'@'%' IDENTIFIED BY 'tonseq-6paxtu-danvUj';
GRANT ALL PRIVILEGES ON appdb.* TO 'appuser'@'%';

FLUSH PRIVILEGES;
