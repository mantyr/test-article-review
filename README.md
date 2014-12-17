Тестовое задание.

mysql> GRANT SELECT, CREATE, INSERT, DELETE, UPDATE  ON test_db.* TO 'test_db'@'localhost' IDENTIFIED BY 'test_123_test_123';
Query OK, 0 rows affected (0.02 sec)

mysql> CREATE DATABASE `test_db`;
Query OK, 1 row affected (0.03 sec)

// создаём таблицы
cd ./configs/
php ./struct.php start default _users;
php ./struct.php start default _role;
php ./struct.php start default _users_role;

php ./struct.php start default _article;
php ./struct.php start default _article_history;
php ./struct.php start default _article_draft;

cd ../
// создаём минимальный набор данных
php ./index.php new_article

// читаем обзоры
php ./index.php get_article $article_id $user_id

// проверяем отдельную установку is_public
php ./index.php set_public $article_id $user_id ok
php ./index.php set_public $article_id $user_id no

// читаем черновики
php ./index.php get_article_draft $article_id $user_id $last_draft           , где $last_draft = 1/0

php ./index.php get_article_draft 4
php ./index.php get_article_draft 4 18

// проверяем фильтр на is_public
php ./index.php set_public 4 20 no
php ./index.php get_article_draft 4

php ./index.php set_public 4 20 ok
php ./index.php get_article_draft 4

php ./index.php set_public 4 20 no
php ./index.php get_article_draft 4
