<?php
/**
 * 路由配置文件
 * 
 * 在这里定义应用的所有路由规则
 */

// 首页路由
$router->get('/', 'HomeController@index');

// 问诊相关路由
$router->get('/consultation', 'ConsultationController@index');
$router->get('/consultation/form', 'ConsultationController@form');
$router->post('/consultation/submit', 'ConsultationController@submit');
$router->post('/consultation/saveDraft', 'ConsultationController@saveDraft');
$router->get('/consultation/result/{id}', 'ConsultationController@result');

// 患者相关路由
$router->get('/patients', 'PatientController@index');
$router->get('/patients/{id}', 'PatientController@show');
$router->post('/patients/create', 'PatientController@create');

// 历史记录路由
$router->get('/history', 'HistoryController@index');
$router->get('/history/{id}', 'HistoryController@show');

// API 路由
$router->post('/api/diagnosis', 'ApiController@diagnosis');
$router->get('/api/export/{id}', 'ApiController@export'); 