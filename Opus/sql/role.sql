/**
 * @Project: opus-skeleton
 * @Version: v1.0.0
 * @Author: Tomasz Ułazowski
 * @Date:   2026-08-22 19:57:51
 * @Last Modified by:   Tomasz Ułazowski
 * @Last Modified time: 2026-08-22 19:58:19
**/

CREATE USER opus_admin
WITH
	PASSWORD 'secret' LOGIN SUPERUSER INHERIT CREATEDB NOCREATEROLE NOREPLICATION;

CREATE USER opus_user
WITH
	PASSWORD 'secret' LOGIN NOSUPERUSER INHERIT NOCREATEDB NOCREATEROLE NOREPLICATION;
