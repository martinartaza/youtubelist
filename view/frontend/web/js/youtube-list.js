define([
    'jquery'
], function ($) {
    'use strict';

    return function (config) {
        var lengthArray = config.videoCount;
        var splitLengthArray = lengthArray / 2;
        var current = 0;
        var widthPerItem, widthPerItemImg, topLeftSlider, diference, totalSlids, entero, coma, entero = 0;

        function carrouselCalculations() {
            totalSlids = lengthArray / 3;
            [entero, coma] = String(totalSlids).split('.', 2);
            entero = entero[0];
            if(typeof coma !== "undefined") {
                coma = coma[0].charAt(0);
            } else {
                coma = 0;
            }
            widthPerItem = $('.youtubeItemContainerItems')[0].clientWidth;
            widthPerItemImg = $('.youtubeItemContainerImg')[0].clientWidth;
            topLeftSlider = (lengthArray * widthPerItem) - widthPerItem;
            diference = 0;

            if (coma > 5) {
                diference = widthPerItem * 2;
            } else if (coma !== 0) {
                diference = widthPerItem;
            }
        }

        $(document).ready(function() {
            if (lengthArray <= splitLengthArray) {
                $('.right-arrow').css('display', 'none');
                $('.left-arrow').css('display', 'none');
            }

            $('#youtubeList .left-arrow').on('click', function() {
                carrouselCalculations();
                if ($('#youtubeItemContainer_' + current).position().left != 0) {
                    if (current > 0) {
                        current = current - 1;
                    } else {
                        current = lengthArray - splitLengthArray;
                    }

                    $(".youtubeCarruselGeneral").animate({
                        "left": -($('#youtubeItemContainer_' + current).position().left)
                    }, 600);

                    return false;
                } else {
                    return false;
                }
            });

            $('#youtubeList .left-arrow').on('hover', function() {
                $(this).css('opacity', '0.5');
            }, function() {
                $(this).css('opacity', '1');
            });

            $('#youtubeList .right-arrow').on('hover', function() {
                $(this).css('opacity', '0.5');
            }, function() {
                $(this).css('opacity', '1');
            });

            $('#youtubeList .right-arrow').on('click', function() {
                carrouselCalculations();

                if($(window).width() > 768) {
                    if (lengthArray <= 2) {
                        return false;
                    }
                }

                if ($('#youtubeItemContainer_' + current).position().left != (topLeftSlider - diference)) {
                    if($(window).width() < 768) {
                        if ((lengthArray - 1) <= current) {
                            return false;
                        }

                        splitLengthArray = 0;
                    }

                    if (lengthArray > current + splitLengthArray) {
                        current = current + 1;
                    } else {
                        current = 0;
                    }

                    $(".youtubeCarruselGeneral").animate({
                        "left": -($('#youtubeItemContainer_' + current).position().left)
                    }, 300);

                    return false;
                } else {
                    return false;
                }
            });

            // Al primer video secundario se le borra la clase current
            if ($(".first").length > 0) {
                $("#youtubeList .youtubeCarruselGeneral .youtubeItemContainerItems").removeClass("current");
                // current le da el contorno azul
                $(".first").addClass("current");
            }

            $('#youtubeList .youtubeCarruselGeneral .youtubeItemContainerItems div img').click(function() {
                $("#youtubeList .youtubeCarruselGeneral .youtubeItemContainerItems").removeClass("current");
                $(this).parent().parent().parent().addClass("current");
                $('#iframeVideo').attr("src", $(this).attr('video'));
            });

            // Cuando haces click en cualquier lugar de la caja que no sea el logo de youtube entras...
            $('#youtubeList .youtubeCarruselGeneral .youtubeItemContainerItems div.youtubeItemContainerImg').click(function() {
                // Les saca el current (Contorno Azul) a los que lo tengan parar ponerselo al nuevo
                $("#youtubeList .youtubeCarruselGeneral .youtubeItemContainerItems").removeClass("current");
                // Agarra el div del click con el ==> $this, va al papa del papa ...x4 al div le agrega el current y lo pone en azul
                $(this).find("img.play-youtube-button").parent().parent().addClass("current");
                // Intercambia el video Principal por el que clikceaste
                $('#iframeVideo').attr("src", $(this).find("img.play-youtube-button").attr('video'));
            });
        });
    };
}); 