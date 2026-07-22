/**
* @Project: Opus
* @Version: 1.0
* @Author: Tomasz Ułazowski
* @Date:   2026-07-11 15:40:12
* @Last Modified by:   Tomasz Ułazowski
* @Last Modified time: 2026-07-13 13:05:01
**/
CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE EXTENSION IF NOT EXISTS dblink;

COMMENT ON DATABASE "opus" IS 'Opus Framework - educational PHP framework with Bootstrap UI, AJAX-driven async pages, CSRF protection, multi-language support, and modular application architecture';

GRANT CONNECT ON DATABASE "opus" TO "opus_user";

GRANT ALL PRIVILEGES ON DATABASE "opus" TO "opus_admin";
