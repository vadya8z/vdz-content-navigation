/**
 * @author Zikiy Vadim
 * @package jQuery Plugins
 * @name vdzShowMore
 * @copyright Copyright (C) 2017 All rights reserved.
 * @link http://online-services.org.ua/
 */

(function ($) {
    $.fn.vdz_sub_menu = function (options) {
        //console.log('options');
        //console.log(options);
        //console.log('search_clock|this');
        //console.log(this);
        if( !(this instanceof $) || (this.length !== 1) ){
            console.log('Error! Not find content element');
            // return;
        }

        var $content_block = this;

        //Основные настройки для плагина (приватные)
        var _settings = {
            'id': 'vdz_sub_menu',
            main_class: 'vdz_sub_menu',
            fixed_id: 'vdz_sub_menu_fixed',
        };

        //Настройки плагина по умолчанию, можно перекрыть
        var default_settings = $.extend({
            headers: 'h1, h2, h3, h4, h5, h6',
            speed : 400,
            class: 'vdz_cn_list',
            before: true,
            after: false,
            fixed: false,
            append_to: 'article',//Default selector
            title: '',
            title_tag: 'h4',
        }, options, _settings);

        if((default_settings.before !== true)
            && (default_settings.after !== true)
            && (default_settings.fixed !== true)){
            return;
        }

        //Создаем меню
        var $sub_menu = $('<div id="'+default_settings.id+'" class="'+default_settings.main_class+'"></div>').append('<ul class="'+default_settings.class+'"></ul>');
       //console.log($sub_menu);

        var href_id_element_num = 1;

        var $headers = $content_block.find(default_settings.headers);
        //console.log($content_block);
        //Проходим каждый заголовок для создания меню
        $headers.each(function () {
            var header_id = 'vdz_cn_' + (href_id_element_num++);
            //Определяем текст заголовков
            var vdz_sub_menu_element_text = $(this).attr('id', header_id).text();
            var $vdz_sub_menu_li = $('<li class="'+header_id+'"></li>');
            var $vdz_sub_menu_a = $('<a></a>', {
                href: '#' + header_id,
                text: vdz_sub_menu_element_text,
            });

            //Добавляем элементы списка из заголовков
            $vdz_sub_menu_li.append($vdz_sub_menu_a).appendTo($sub_menu.find('ul'));
        });

        //Добавляем заголовок
        if( default_settings.title !== '' ){
            $sub_menu.prepend($('<'+default_settings.title_tag+'>'+default_settings.title+'</'+default_settings.title_tag+'>'));
        }

        //Добавляем к документу меню
        if(default_settings.before === true){
            $sub_menu.prependTo(default_settings.append_to);
        }
        if(default_settings.after === true){
            $sub_menu.clone().removeAttr('id').appendTo(default_settings.append_to);
        }
        // if(default_settings.fixed === true){
        //
        // }


        //Анимация при скролле
        $('.'+default_settings.main_class+'> ul > li > a').on('click', function(e){
            e.preventDefault();
            var target = $(this).attr('href');
            var top_offset = $(target).offset().top;
            $('html:not(:animated),body:not(:animated)').animate({ scrollTop: top_offset}, default_settings.speed, function() {
                window.location.hash = target;
            });
            return false;
        });

        return $content_block;

    };

    $(document).ready(function($){
        $('.vdz_cn_shortcode').each(function(){
            $($(this).data('vdz_cn_shortcode_find_in_selector') || 'article').vdz_sub_menu({
                    //'headers' : 'h2, h3',
                    //'after': true,
                    'append_to': $(this),
                    'speed': $(this).data('vdz_cn_shortcode_speed') || 400,
                    'title': $(this).data('vdz_cn_shortcode_title') || '',
                })
            });
    });
})(jQuery);
