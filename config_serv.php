<?php
	define('HOST', 'localhost');// Define the database host // definindo o host do banco de dados
	define('USER', 'root');//usuario
	define('PASS', '');//password from the database
	define('BASE', '');// select the database name // selecionando o nome do banco de dados
    define('PORT', '');//port // porta do banco de dados

	$conn = new MySQLi(HOST,USER,PASS,BASE);