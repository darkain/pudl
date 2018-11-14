<?php


////////////////////////////////////////////////////////////////////////////////
// A COLLECTION OF INTERNAL PROTECTED/PRIVATE METHODS USED BY PUDL
////////////////////////////////////////////////////////////////////////////////

trait pudlInternal {


	protected static function _engine($type) {

		switch (strtoupper($type)) {

			// LEGACY MYSQL_ FUNCTIONS
			case 'MYSQL-DEPRECATED':
				return ['MySql',	'/mysql/pudlMySql.php'];
			break;


			// MODERN MYSQLI_ FUNCTIONS
			case 'MYSQL':
			case 'MYSQLI':
			case 'MARIA':
			case 'MARIADB':
			case 'PERCONA':
				return ['MySqli',	'/mysql/pudlMySqli.php'];
			break;


			// MYSQLI_ FUNCTION W/ GALERA CLUSTERING SUPPORT
			case 'GALERA':
				return ['Galera',	'/mysql/pudlGalera.php'];
			break;


			// PGSQL_ FUNCTIONS
			case 'PGSQL':
			case 'POSTGRESQL':
				return ['PgSql',	'/pgsql/pudlPgSql.php'];
			break;


			// LEGACY MSSQL_ FUNCTIONS
			case 'MSSQL-DEPRECATED':
				return ['MsSql',	'/mssql/pudlMsSql.php'];
			break;


			// MODERN SQLSRV_ FUNCTIONS
			case 'MSSQL':
			case 'SQLSRV':
			case 'SQLSERVER':
			case 'MICROSOFT':
				return ['SqlSrv',	'/mssql/pudlSqlSrv.php'];
			break;


			// MODERN SQLITE3_ FUNCTIONS
			case 'FILE':
			case 'SQLITE':
			case 'SQLITE3':
				return ['Sqlite',	'/sqlite/pudlSqlite.php'];
			break;


			// ODBC_ FUNCTIONS
			case 'ODBC':
				return ['Odbc',		'/odbc/pudlOdbc.php'];
			break;


			// PDO:: OBJECT
			case 'PDO':
				return ['Pdo',		'/sql/pudlPdo.php'];
			break;


			// /DEV/NULL ENGINE
			case 'NULL':
				return ['Null',		'/null/pudlNull.php'];
			break;
		}


		// *NOT* /DEV/NULL ENGINE
		// INSTEAD, NOT FOUND / ERROR
		return NULL;
	}

}
