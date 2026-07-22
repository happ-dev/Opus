/**
* @Project: Opus
* @Version: 1.0
* @Author: Tomasz Ułazowski
* @Date:   2026-07-13 15:13:31
* @Last Modified by:   Tomasz Ułazowski
* @Last Modified time: 2026-07-14 11:14:21
**/

-- =============================================================================
-- Schema: demo
-- Description: Demonstration schema for Opus Framework DataTables features
-- =============================================================================
CREATE SCHEMA "demo";
COMMENT ON SCHEMA "demo" IS 'Demonstration schema for Opus Framework DataTables features';

-- Grants: schema
GRANT ALL PRIVILEGES ON SCHEMA "demo" TO "opus_admin";
GRANT USAGE ON SCHEMA "demo" TO "opus_user";

-- =============================================================================
-- Table: demo.departments
-- Description: Department dictionary for payroll demo
-- =============================================================================
CREATE TABLE IF NOT EXISTS demo.departments (
	id__department		SMALLINT,
	name				CHARACTER VARYING(32) NOT NULL,
	CONSTRAINT pk_departments PRIMARY KEY (id__department)
);

-- Grants: demo.departments
GRANT SELECT ON TABLE demo.departments TO opus_user;

-- Comments: demo.departments
COMMENT ON COLUMN demo.departments.id__department IS 'Identifier|demo.table.db.departments.id';
COMMENT ON COLUMN demo.departments.name IS 'Department name|demo.table.db.departments.name';

-- =============================================================================
-- Table: demo.payroll
-- Description: Employee payroll records for DataTables demonstration
-- =============================================================================
CREATE TABLE IF NOT EXISTS demo.payroll (
	id__payroll			SERIAL,
	firstname			CHARACTER VARYING(32) NOT NULL,
	lastname			CHARACTER VARYING(32) NOT NULL,
	department			SMALLINT NOT NULL,
	position			CHARACTER VARYING(48),
	salary				NUMERIC(10,2) NOT NULL,
	contract			SMALLINT NOT NULL DEFAULT 1,
	active				BOOLEAN NOT NULL DEFAULT TRUE,
	hire_date			DATE NOT NULL DEFAULT CURRENT_DATE,
	CONSTRAINT pk_payroll PRIMARY KEY (id__payroll),
	CONSTRAINT fk_department FOREIGN KEY (department) REFERENCES demo.departments(id__department)
);

-- Indexes: demo.payroll
CREATE INDEX idx_payroll_department ON demo.payroll (department);
CREATE INDEX idx_payroll_active ON demo.payroll (active);

-- Grants: demo.payroll
GRANT SELECT, INSERT, UPDATE, DELETE ON TABLE demo.payroll TO opus_user;
GRANT SELECT, UPDATE ON SEQUENCE demo.payroll_id__payroll_seq TO opus_user;

-- Comments: demo.payroll
COMMENT ON COLUMN demo.payroll.id__payroll IS 'Identifier|demo.table.db.payroll.id';
COMMENT ON COLUMN demo.payroll.firstname IS 'First name|demo.table.db.payroll.firstname';
COMMENT ON COLUMN demo.payroll.lastname IS 'Last name|demo.table.db.payroll.lastname';
COMMENT ON COLUMN demo.payroll.department IS 'Department|demo.table.db.payroll.department';
COMMENT ON COLUMN demo.payroll.position IS 'Position|demo.table.db.payroll.position';
COMMENT ON COLUMN demo.payroll.salary IS 'Salary|demo.table.db.payroll.salary';
COMMENT ON COLUMN demo.payroll.contract IS 'Contract type|demo.table.db.payroll.contract';
COMMENT ON COLUMN demo.payroll.active IS 'Active|demo.table.db.payroll.active';
COMMENT ON COLUMN demo.payroll.hire_date IS 'Hire date|demo.table.db.payroll.hire_date';

-- =============================================================================
-- Table: demo.bonuses
-- Description: Bonus records linked to payroll - demonstrates button + form
-- =============================================================================
CREATE TABLE IF NOT EXISTS demo.bonuses (
	id__bonus			SERIAL,
	id_to_payroll		INTEGER NOT NULL,
	percent				NUMERIC(5,2) NOT NULL,
	amount				NUMERIC(10,2) NOT NULL,
	granted				BOOLEAN NOT NULL DEFAULT FALSE,
	reason				CHARACTER VARYING(128),
	pay_date			DATE NOT NULL DEFAULT DATE_TRUNC('month', CURRENT_DATE),
	CONSTRAINT pk_bonuses PRIMARY KEY (id__bonus),
	CONSTRAINT fk_payroll FOREIGN KEY (id_to_payroll) REFERENCES demo.payroll(id__payroll),
	CONSTRAINT uq_payroll_pay_date UNIQUE (id_to_payroll, pay_date)
);

-- Indexes: demo.bonuses
CREATE INDEX idx_bonuses_payroll ON demo.bonuses (id_to_payroll);
CREATE INDEX idx_bonuses_pay_date ON demo.bonuses (pay_date);

-- Grants: demo.bonuses
GRANT SELECT, INSERT, UPDATE ON TABLE demo.bonuses TO opus_user;
GRANT SELECT, UPDATE ON SEQUENCE demo.bonuses_id__bonus_seq TO opus_user;

-- Comments: demo.bonuses
COMMENT ON COLUMN demo.bonuses.id__bonus IS 'Identifier|demo.table.db.bonuses.id';
COMMENT ON COLUMN demo.bonuses.id_to_payroll IS 'Employee|demo.table.db.bonuses.payroll';
COMMENT ON COLUMN demo.bonuses.percent IS 'Bonus percent|demo.table.db.bonuses.percent';
COMMENT ON COLUMN demo.bonuses.amount IS 'Bonus amount|demo.table.db.bonuses.amount';
COMMENT ON COLUMN demo.bonuses.granted IS 'Granted|demo.table.db.bonuses.granted';
COMMENT ON COLUMN demo.bonuses.reason IS 'Reason|demo.table.db.bonuses.reason';
COMMENT ON COLUMN demo.bonuses.pay_date IS 'Pay period|demo.table.db.bonuses.pay_date';

-- =============================================================================
-- Data: demo.departments
-- Description: Default department dictionary
-- =============================================================================
INSERT INTO demo.departments (id__department, name) VALUES (1, 'IT');
INSERT INTO demo.departments (id__department, name) VALUES (2, 'HR');
INSERT INTO demo.departments (id__department, name) VALUES (3, 'Finance');
INSERT INTO demo.departments (id__department, name) VALUES (4, 'Marketing');
INSERT INTO demo.departments (id__department, name) VALUES (5, 'Operations');

-- =============================================================================
-- Data: demo.payroll
-- Description: Sample employee payroll records
-- =============================================================================
INSERT INTO demo.payroll (firstname, lastname, department, position, salary, contract, active, hire_date) VALUES
	('Anna',      'Kowalska',    1, 'Senior Developer',      12500.00, 1, TRUE,  '2021-03-15'),
	('Marek',     'Nowak',       1, 'Junior Developer',       7200.00, 1, TRUE,  '2023-06-01'),
	('Katarzyna', 'Wiśniewska',  2, 'HR Manager',           11000.00, 1, TRUE,  '2019-09-10'),
	('Piotr',     'Zieliński',   3, 'Accountant',            9800.00, 2, TRUE,  '2022-01-20'),
	('Ewa',       'Dąbrowska',   4, 'Marketing Specialist',  8500.00, 1, TRUE,  '2022-11-05'),
	('Tomasz',    'Lewandowski', 5, 'Operations Lead',      10200.00, 1, TRUE,  '2020-07-01'),
	('Joanna',    'Wójcik',      1, 'DevOps Engineer',      13000.00, 3, TRUE,  '2021-08-22'),
	('Adam',      'Kamiński',    3, 'Financial Analyst',     9200.00, 2, FALSE, '2020-02-14');
