<?php
    error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);

    xhprof_enable(XHPROF_FLAGS_MEMORY);

    include_once('./systems/core.php');

    // импортируем глобальные переменные
    if ($arr_params = globals_params()) extract($arr_params, EXTR_SKIP);

    import('systems.db');
    import('systems.config');

    import('systems.article');

    $db = db('default');
    $article = new article($db);

    list(, $_command, $_article_id, $_user_id, $_is_public) = $argv;

    if ($_command == '') die('php ./index.php new_article|draft_update|draft_update_2|set_public|get_article|get_article_draft [$article_id|0] [$user_id|0] [$is_public = ok|no]');

    if ($_command == 'new_article') {

        $user_id = 10;
        for ($i = 1; $i < 10; $i++) {

            $article->save_article(array(
                'title' => "title_$i",
                'body_text' => "body_text_$i",
                'user_id' => $user_id,
            ));
        }

        sleep(2);

        $user_id = 12;
        for ($i = 4; $i < 8; $i++) {

            $article->save_article(array(
                'article_id' => $i,
                'title' => "title_{$i}_update",
                'body_text' => "body_text_{$i}_update",
                'user_id' => $user_id,
            ));
        }

        sleep(2);

        $user_id = 14;
        for ($i = 2; $i < 5; $i++) {

            $article->save_article(array(
                'article_id' => $i,
                'title' => "title_{$i}_draft",
                'body_text' => "body_text_{$i}_draft",
                'user_id' => $user_id,
                'is_draft' => true,
            ));
        }

    }
    if ($_command == 'draft_update') {
        $user_id = 14;
        for ($i = 3; $i < 7; $i++) {

            $article->save_article(array(
                'article_id' => $i,
                'title' => "title_{$i}_draft_update",
                'body_text' => "body_text_{$i}_draft_update",
                'user_id' => $user_id,
                'is_draft' => true,
            ));
        }
    }
    if ($_command == 'draft_update_2') {
        $i = 4;
        $user_id = 14;
        $article->save_article(array(
            'article_id' => $i,
            'title' => "title_{$i}_draft_update_2",
            'body_text' => "body_text_{$i}_draft_update_2",
            'user_id' => $user_id,
            'is_draft' => true,
        ));
        $i = 4;
        $user_id = 18;
        $article->save_article(array(
            'article_id' => $i,
            'title' => "title_{$i}_draft_update_2",
            'body_text' => "body_text_{$i}_draft_update_2",
            'user_id' => $user_id,
            'is_draft' => true,
        ));
    }


    if ($_command == 'set_public') {
        $data = $article->get_article($_article_id);
        echo var_export($data, true);

        $article->set_public($_article_id, $_user_id, $_is_public);

        $data = $article->get_article($_article_id);
        echo var_export($data, true);
    }
    if ($_command == 'get_article') {
        $data = $article->get_article($_article_id, $_user_id);
        echo var_export($data, true);
    }
    if ($_command == 'get_article_draft') {
        $data = $article->get_article_draft($_article_id, $_user_id);
        echo var_export($data, true);
    }

