<?php
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! isset( $allowed_html['li'] )) {

    $allowed_html_list = array(
                                'aria-controls'    => 1,
                                'aria-current'     => 1,
                                'aria-describedby' => 1,
                                'aria-details'     => 1,
                                'aria-expanded'    => 1,
                                'aria-hidden'      => 1,
                                'aria-label'       => 1,
                                'aria-labelledby'  => 1,
                                'aria-live'        => 1,
                                'class'            => 1,
                                'data-*'           => 1,
                                'dir'              => 1,
                                'hidden'           => 1,
                                'id'               => 1,
                                'lang'             => 1,
                                'style'            => 1,
                                'title'            => 1,
                                'role'             => 1,
                                'xml:lang'         => 1,
                            );

    $allowed_html['li'] = $allowed_html_list;
    $allowed_html['ul'] = $allowed_html_list;
}

if ( ! isset( $allowed_html['table'] )) {

    $allowed_html['table'] = array(
                                    'align'            => 1,
                                    'bgcolor'          => 1,
                                    'border'           => 1,
                                    'cellpadding'      => 1,
                                    'cellspacing'      => 1,
                                    'dir'              => 1,
                                    'rules'            => 1,
                                    'summary'          => 1,
                                    'width'            => 1,
                                    'aria-controls'    => 1,
                                    'aria-current'     => 1,
                                    'aria-describedby' => 1,
                                    'aria-details'     => 1,
                                    'aria-expanded'    => 1,
                                    'aria-hidden'      => 1,
                                    'aria-label'       => 1,
                                    'aria-labelledby'  => 1,
                                    'aria-live'        => 1,
                                    'class'            => 1,
                                    'data-*'           => 1,
                                    'hidden'           => 1,
                                    'id'               => 1,
                                    'lang'             => 1,
                                    'style'            => 1,
                                    'title'            => 1,
                                    'role'             => 1,
                                    'xml:lang'         => 1,
                                );

    $table_th_td = array(
                            'abbr'             => 1,
                            'align'            => 1,
                            'axis'             => 1,
                            'bgcolor'          => 1,
                            'char'             => 1,
                            'charoff'          => 1,
                            'colspan'          => 1,
                            'dir'              => 1,
                            'headers'          => 1,
                            'height'           => 1,
                            'nowrap'           => 1,
                            'rowspan'          => 1,
                            'scope'            => 1,
                            'valign'           => 1,
                            'width'            => 1,
                            'aria-controls'    => 1,
                            'aria-current'     => 1,
                            'aria-describedby' => 1,
                            'aria-details'     => 1,
                            'aria-expanded'    => 1,
                            'aria-hidden'      => 1,
                            'aria-label'       => 1,
                            'aria-labelledby'  => 1,
                            'aria-live'        => 1,
                            'class'            => 1,
                            'data-*'           => 1,
                            'hidden'           => 1,
                            'id'               => 1,
                            'lang'             => 1,
                            'style'            => 1,
                            'title'            => 1,
                            'role'             => 1,
                            'xml:lang'         => 1,
                        );

    $allowed_html['th'] = $table_th_td;
    $allowed_html['td'] = $table_th_td;

    $allowed_html['tr'] = array(
                                    'align'            => 1,
                                    'bgcolor'          => 1,
                                    'char'             => 1,
                                    'charoff'          => 1,
                                    'valign'           => 1,
                                    'aria-controls'    => 1,
                                    'aria-current'     => 1,
                                    'aria-describedby' => 1,
                                    'aria-details'     => 1,
                                    'aria-expanded'    => 1,
                                    'aria-hidden'      => 1,
                                    'aria-label'       => 1,
                                    'aria-labelledby'  => 1,
                                    'aria-live'        => 1,
                                    'class'            => 1,
                                    'data-*'           => 1,
                                    'dir'              => 1,
                                    'hidden'           => 1,
                                    'id'               => 1,
                                    'lang'             => 1,
                                    'style'            => 1,
                                    'title'            => 1,
                                    'role'             => 1,
                                    'xml:lang'         => 1,
                                );
}
