<?php

namespace App\Controllers;

use App\Core\Controller;

/**
 * 首页控制器
 */
class HomeController extends Controller
{
    /**
     * 首页
     */
    public function index()
    {
        $data = [
            'title' => '中医智能问诊系统',
            'description' => '基于人工智能的中医辅助诊断平台'
        ];
        
        $this->view('home/index', $data);
    }
} 