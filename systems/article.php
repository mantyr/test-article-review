<?php

    /**
     * Статьи и обзоры
     * @author Oleg Shevelev|mantyr@gmail.com
     */

    class article {
        private $db = false;

        public function __construct($db = false) {
            $this->db = $db;
        }

        /**
         * Стоит хранить отдельно user_id для того кто установил is_public
         */

        public function set_public($article_id = false, $user_id = false, $is_public = false) {
            if (!$article_id || !$user_id || !in_array($is_public, array('ok', 'no'))) return false;
            $query = "
                UPDATE `_article`
                SET
                    is_public = '".$is_public."'
                    ,updater_id = '".(int)$user_id."'
                WHERE article_id = '".(int)$article_id."'
            ";
            $this->db->q($query);

            // добавляем в историю
            $this_date = date("Y:m:d H:i:s", time());
            $query = "
                INSERT INTO `_article_history`
                SET
                    article_id   = '".(int)$article_id."'
                    ,creator_id  = '".(int)$user_id."'
                    ,create_date = '".$this_date."'
                    ,is_public   = '".$is_public."'
            ";
            $this->db->q($query);
        }

        public function save_article($data = array()) {
            $article_id = (int)$data['article_id'];
            $user_id    = (int)$data['user_id'];
            $title      = mysql_escape_string($data['title']);
            $body_text  = mysql_escape_string($data['body_text']);
            $is_public  = $data['is_public'];
            $is_draft   = $data['is_draft'];


            if (!$user_id || (!$title && !$body_text)) return false;
            $this_date = date("Y:m:d H:i:s", time());

            if ($is_draft) {
                // сохраняем черновик (один черновик на одного пользователя к каждой статье)
                if (!$article_id || !$user_id || (!$title && !$body_text)) return false;
                $query = "
                    INSERT INTO `_article_draft`
                    SET
                        article_id   = '".(int)$article_id."'
                        ,creator_id  = '".(int)$user_id."'
                        ,title       = '".mysql_escape_string($title)."'
                        ,body_text   = '".mysql_escape_string($body_text)."'
                        ,create_date = '".$this_date."'
                    ON DUPLICATE KEY UPDATE
                        title        = '".mysql_escape_string($title)."'
                        ,body_text   = '".mysql_escape_string($body_text)."'
                        ,create_date = '".$this_date."'
                ";
                $this->db->q($query);
            } else {
                $query = "
                    INSERT INTO `_article`
                    SET
                        creator_id  = '".$user_id."'
                        ".($article_id ? ",article_id   = '".$article_id."'" : "")."
                        ,updater_id  = '".$user_id."'
                        ,create_date = '".$this_date."'
                        ,update_date = '".$this_date."'
                        ,title       = '".mysql_escape_string($title)."'
                        ,body_text   = '".mysql_escape_string($body_text)."'
                        ".(in_array($is_public, array('ok', 'no')) ? ",is_public = '".$is_public."'" : "")."
                    ";
                if ($article_id) {
                    $query .= "
                        ON DUPLICATE KEY UPDATE
                            updater_id   = '".$user_id."'
                            ,update_date = '".$this_date."'
                            ,title       = '".mysql_escape_string($title)."'
                            ,body_text   = '".mysql_escape_string($body_text)."'
                            ".(in_array($is_public, array('ok', 'no')) ? ",is_public = '".$is_public."'" : "")."
                    ";
                }
                $this->db->q($query);

                if (!$article_id) $article_id = $this->db->lastInsertId('_article');

                // добавляем в историю
                $query = "
                    INSERT INTO `_article_history`
                    SET
                        article_id   = '".(int)$article_id."'
                        ,creator_id  = '".(int)$user_id."'
                        ,create_date = '".$this_date."'
                        ,title       = '".mysql_escape_string($title)."'
                        ,body_text   = '".mysql_escape_string($body_text)."'
                        ".(in_array($is_public, array('ok', 'no')) ? ",is_public = '".$is_public."'" : "")."
                ";
                $this->db->q($query);
                $history_id = $this->db->lastInsertId('_article_history');
                return array($article_id, $history_id);
            }
        }

        public function get_article($article_id = false, $creator_id = false, $updater_id = false, $is_public = false) {
            if (!$article_id && !$creator_id && !$updater_id) return false;

            if ($article_id) {
                if (is_array($article_id)) {
                    $article_id = array_map(function($id) { return (int)$id; }, $article_id);

                    $_WHERE[] = "a.article_id IN ('".implode("', '", $article_id)."')";
                } else {
                    $_WHERE[] = "a.article_id = '".(int)$article_id."'";
                }
            }
            if ($creator_id) $_WHERE[] = "a.creator_id = '".(int)$creator_id."'";
            if ($updater_id) $_WHERE[] = "a.updater_id = '".(int)$updater_id."'";
            if (in_array($is_public, array('ok', 'no'))) $_WHERE[] = "is_public = '".$is_public."'";

            $query = "
                SELECT
                    a.article_id
                    ,a.creator_id
                    ,a.updater_id
                    ,a.body_text
                    ,a.create_date
                    ,a.update_date
                    ,IF(a.creator_id = a.updater_id AND a.create_date = a.update_date, false, true) as is_update
                    ,IF(a.is_public = 'ok', true, false) as is_public
                FROM `_article` a
                ".($_WHERE ? "WHERE ".implode(" AND ", $_WHERE) : "")."
            ";
            if ($res = $this->db->q($query)) {
                while ($row = $res->fetch()) {
                    if ($article_id && !is_array($article_id)) return $row;
                    $data[$row['article_id']] = $row;
                }
            }
            return $data;
        }

        /**
         * Есть черновики к опубликованным и не опубликованным статьям/обзорам, показываем только к неопубликованным
         * $article_id && $user_id = один черновик
         * $article_id || $user_id = множество черновиков
         */

        public function get_article_draft($article_id = false, $user_id = false) {
            if (!$article_id && !$user_id) return false;
            if ($article_id) $_WHERE[] = "d.article_id = '".(int)$article_id."'";
            if ($user_id)    $_WHERE[] = "d.creator_id = '".(int)$user_id."'";

            $query = "
                SELECT
                    d.article_id
                    ,d.creator_id  as draft_creator_id
                    ,d.create_date as draft_create_date
                    ,d.title       as draft_title
                    ,d.body_text   as draft_body_text

                    ,a.creator_id
                    ,a.create_date
                    ,a.updater_id
                    ,a.update_date
                    ,a.title
                    ,a.body_text
                    ,false as is_public
                FROM `_article_draft` d
                    INNER JOIN `_article` a ON (d.article_id = a.article_id && a.is_public != 'ok')
                ".($_WHERE ? "WHERE ".implode(" AND ", $_WHERE) : "")."
            ";
            if ($res = $this->db->q($query)) {
                while ($row = $res->fetch()) {
                    if ($article_id && $user_id) return $row;
                    $data[$row['article_id']][$row['draft_creator_id']] = $row;
                }
            }
            return $data;
        }
    }


