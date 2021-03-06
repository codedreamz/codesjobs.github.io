<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * ko.php
 * Author     : DRSN
 * Date       : 2017/04/01
 * Version    : 0.1
 * Description: 韩文试行
 */
class Lang_ko extends Lang {

    /**
     * @return string 返回语言名称
     */
    public function name() {
        return "한극어";
    }

    /**
     * @return array 返回包含翻译文本的数组
     */
    public function translated() {
        return array(
            // Post
            '阅读: %d' => '열람: %d',
            '编辑' => '편집',
            '标签: ' => '태그: ',
            '无' => '없습니다',
            '返回文章列表' => '글 목록',
            '文章二维码' => 'QR 코드',
            '打赏' => '상금',

            // 页眉 head
            '分类 %s 下的文章' => '카테고리: %s',
            '包含关键字 %s 的文章' => '포함: %s',
            '标签 %s 下的文章' => '태그: %s',
            '%s 发布的文章' => '글 작자: %s',

            '当前网页 <strong>不支持</strong> 你正在使用的浏览器. 为了正常的访问, 请 <a href="%s">升级你的浏览器</a>.' => '웹 브라우저가 <strong>너무 낡은</strong> 경우. 웹 브라우저가 <a href="%s">업그레이드</a> 해주세요.',

            // 评论 Comments
            '评论' => '댓글',
            '1 条评论' => '댓글: 1',
            '%d 条评论' => '댓글: %d',
            '评论列表' => '댓글 목록',
            '添加新评论' => '댓글 등록',
            '提交评论' => '발사!',
            '称呼' => '이름',
            '电子邮件' => '이메일',
            '网站' => '웹사이트',
            '回复' => '답글',
            '在这里输入你的评论...' => '덧붙임 글을 입력해주세오...',
            '<strong>不接收</strong>回复邮件通知' => '이메일 알림<strong> 안받게습니다</strong>',

            //参数分别为: 用户链接、用户名、登出链接
            '登录为 <a href="%s">%s</a>. <a href="%s" title="Logout">退出 &raquo;</a>' => '<a href="%s">%s</a>님. <a href="%s" title="Logout">로그아웃 &raquo;</a>',

            '登录为' => '로그인: ',
            '退出' => '로그아웃',

            // 列表 List
            '阅读全文' => '전체보기',
            '没有找到内容' => '검색결과가 없습니다',

            // 归档 Archives
            '标签云' => '태그',
            '时间归档' => '글 목록',
            '归档' => '글 목록',

            // 404页面 404
            '页面未找到' => '페이지를 찾을 수 없음니다.- -',

            // 侧边栏 Side Menu
            '搜索...' => '검색...',
            '控制台' => '사이트 관리',
            '首页' => '블로그 홈',
            '关于' => '나의정보',

            '文章列表' => '글 목록',
            '友链' => '이웃링크',
            '留言板' => '류언부',

            '友情链接' => '이웃링크',
            '文章分类' => '카테고리',
            '分类' => '카테고리',
            '夜间模式' => '야간모드',
            '日间模式' => '낮에모드',
            '自动模式' => '자동모드',
            '文章目录' => '글 목록',


            // 页脚 Footer
            '本页链接的二维码' => '이 페이지로 연결하는 QR코드',
            '打赏二维码' => '상금 QR 코드',
            '上一篇: ' => '이전의: ',
            '下一篇: ' => '다음의: ',
            '没有了' => '없습니다',

            // 日期格式化'
            '%d 年前'   => '%d 년 전',
            '%d 个月前' => '%d 달 전',
            '%d 星期前' => '%d 주일 전',
            '%d 天前'   => '%d 일 전',
            '%d 小时前' => '%d 시간 전',
            '%d 分钟前' => '%d 분 전',
            '%d 秒前'   => '%d 초 전',
            '1 年前'   => '일년 전',
            '1 个月前' => '한달 전',
            '1 星期前' => '일주일 전',
            '1 天前'   => '하루 전',
            '1 小时前' => '한시간 전',
            '1 分钟前' => '일분 전',
            '1 秒前'   => '일초 전',
            '昨天 %s'   => '어제 %s',

        );
    }

    /**
     * @return string 返回日期的格式化字符串
     */
    public function dateFormat() {
        return "Y 년 m 월 d 일";
    }
}