START TRANSACTION;
SET standard_conforming_strings=off;
SET escape_string_warning=off;
SET CONSTRAINTS ALL DEFERRED;

CREATE TABLE "dnssec_keys" (
    "id" integer NOT NULL,
    "dszone" integer NOT NULL,
    "filename" varchar(100) NOT NULL,
    "fkey" text ,
    "fprivate" text ,
    "archive" varchar(6) DEFAULT NULL,
    "refresh" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE ("id")
);

CREATE TABLE "dnssec_zones" (
    "id" integer NOT NULL,
    "zone" integer NOT NULL,
    "krf" text NOT NULL,
    "dsset" text NOT NULL,
    UNIQUE ("id")
);

CREATE TABLE "options" (
    "prefkey" varchar(40) NOT NULL,
    "preftype" varchar(12) NOT NULL DEFAULT '',
    "prefval" varchar(510) DEFAULT NULL,
    UNIQUE ("prefkey")
);

INSERT INTO "options" VALUES ('A','record','on'),('A6','record','off'),('AAAA','record','off'),('AFSDB','record','off'),('APL','record','off'),('ATMA','record','off'),('AXFR','record','off'),('CERT','record','off'),('CNAME','record','on'),('DNAME','record','off'),('DNSKEY','record','off'),('DS','record','off'),('EID','record','off'),('GPOS','record','off'),('HINFO','record','off'),('hostmaster','normal','postmaster.your.ns'),('ISDN','record','off'),('IXFR','record','off'),('KEY','record','off'),('KX','record','off'),('LOC','record','off'),('MAILB','record','off'),('master','normal','0.0.0.0'),('MINFO','record','off'),('MX','record','on'),('NAPTR','record','off'),('NIMLOC','record','off'),('NS','record','on'),('NSAP','record','off'),('NSAP-PTR','record','off'),('NSEC','record','off'),('NXT','record','off'),('OPT','record','off'),('prins','normal','your.master.ns'),('PTR','record','off'),('PX','record','off'),('range','normal','10'),('RP','record','off'),('RRSIG','record','off'),('RT','record','off'),('secns','normal','your.sec.ns'),('SIG','record','off'),('SINK','record','off'),('SRV','record','on'),('SSHFP','record','off'),('TKEY','record','off'),('TSIG','record','off'),('TXT','record','on'),('WKS','record','off'),('X25','record','off');
CREATE TABLE "records" (
    "id" integer NOT NULL,
    "zone" integer NOT NULL DEFAULT '0',
    "host" varchar(256) NOT NULL,
    "ttl" integer DEFAULT NULL,
    "type" varchar(16) NOT NULL,
    "pri" integer NOT NULL DEFAULT '0',
    "destination" varchar(8192) DEFAULT NULL,
    UNIQUE ("id")
);

CREATE TABLE "slave_zones" (
    "id" integer NOT NULL,
    "name" varchar(256) NOT NULL,
    "master" varchar(256) DEFAULT NULL,
    "owner" integer NOT NULL DEFAULT '0',
    "updated" varchar(6) NOT NULL DEFAULT 'yes',
    "valid" varchar(6) NOT NULL DEFAULT 'may',
    UNIQUE ("id"),
    UNIQUE ("name")
);

CREATE TABLE "users" (
    "id" integer NOT NULL,
    "username" varchar(64) NOT NULL,
    "realname" varchar(100) DEFAULT NULL,
    "password" varchar(64) NOT NULL,
    "admin" varchar(6) NOT NULL DEFAULT 'no',
    UNIQUE ("id"),
    UNIQUE ("username")
);

INSERT INTO "users" VALUES (1,'admin','Administrator','3c99cbdb5c15684e4fc190f4f17e443c','yes');
CREATE TABLE "zones" (
    "id" integer NOT NULL,
    "name" varchar(128) NOT NULL,
    "pri_dns" varchar(256) DEFAULT NULL,
    "sec_dns" varchar(256) DEFAULT NULL,
    "serial" integer NOT NULL DEFAULT '0',
    "refresh" integer NOT NULL DEFAULT '604800',
    "retry" integer NOT NULL DEFAULT '86400',
    "expire" integer NOT NULL DEFAULT '2419200',
    "ttl" integer NOT NULL DEFAULT '604800',
    "owner" integer NOT NULL DEFAULT '1',
    "valid" varchar(6) NOT NULL DEFAULT 'may',
    "updated" varchar(6) NOT NULL DEFAULT 'yes',
    "secured" varchar(6) NOT NULL DEFAULT 'no',
    UNIQUE ("id"),
    UNIQUE ("name")
);


-- Post-data save --
COMMIT;
START TRANSACTION;

-- Typecasts --

-- Foreign keys --
ALTER TABLE "dnssec_keys" ADD CONSTRAINT "fkdskeys" FOREIGN KEY ("dszone") REFERENCES "dnssec_zones" ("id") ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;
CREATE INDEX ON "dnssec_keys" ("dszone");
ALTER TABLE "dnssec_zones" ADD CONSTRAINT "fkdszones" FOREIGN KEY ("zone") REFERENCES "zones" ("id") ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;
CREATE INDEX ON "dnssec_zones" ("zone");
ALTER TABLE "records" ADD CONSTRAINT "fkrecords" FOREIGN KEY ("zone") REFERENCES "zones" ("id") ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;
CREATE INDEX ON "records" ("zone");

-- Sequences --
CREATE SEQUENCE dnssec_keys_id_seq;
SELECT setval('dnssec_keys_id_seq', max(id)) FROM dnssec_keys;
ALTER TABLE "dnssec_keys" ALTER COLUMN "id" SET DEFAULT nextval('dnssec_keys_id_seq');
CREATE SEQUENCE dnssec_zones_id_seq;
SELECT setval('dnssec_zones_id_seq', max(id)) FROM dnssec_zones;
ALTER TABLE "dnssec_zones" ALTER COLUMN "id" SET DEFAULT nextval('dnssec_zones_id_seq');
CREATE SEQUENCE records_id_seq;
SELECT setval('records_id_seq', max(id)) FROM records;
ALTER TABLE "records" ALTER COLUMN "id" SET DEFAULT nextval('records_id_seq');
CREATE SEQUENCE slave_zones_id_seq;
SELECT setval('slave_zones_id_seq', max(id)) FROM slave_zones;
ALTER TABLE "slave_zones" ALTER COLUMN "id" SET DEFAULT nextval('slave_zones_id_seq');
CREATE SEQUENCE users_id_seq;
SELECT setval('users_id_seq', max(id)) FROM users;
ALTER TABLE "users" ALTER COLUMN "id" SET DEFAULT nextval('users_id_seq');
CREATE SEQUENCE zones_id_seq;
SELECT setval('zones_id_seq', max(id)) FROM zones;
ALTER TABLE "zones" ALTER COLUMN "id" SET DEFAULT nextval('zones_id_seq');

-- Full Text keys --

COMMIT;
