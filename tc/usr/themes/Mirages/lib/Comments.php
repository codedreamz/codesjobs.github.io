<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 评论归档
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * 评论归档组件
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Mirages_Widget_Comments_Archive extends Widget_Abstract_Comments
{
    /**
     * 当前页
     *
     * @access private
     * @var integer
     */
    private $_currentPage;

    /**
     * 所有文章个数
     *
     * @access private
     * @var integer
     */
    private $_total = false;

    /**
     * 子父级评论关系
     *
     * @access private
     * @var array
     */
    private $_threadedComments = array();
    
    /**
     * 多级评论回调函数
     * 
     * @access private
     * @var mixed
     */
    private $_customThreadedCommentsCallback = false;

    /**
     * _singleCommentOptions  
     * 
     * @var mixed
     * @access private
     */
    private $_singleCommentOptions = NULL;


    private $_commentAuthors = array();

    /**
     * 构造函数,初始化组件
     *
     * @access public
     * @param mixed $request request对象
     * @param mixed $response response对象
     * @param mixed $params 参数列表
     * @return void
     */
    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);
        $this->parameter->setDefault('parentId=0&commentPage=0&commentsNum=0&allowComment=1');
        
        /** 初始化回调函数 */
        if (function_exists('threadedComments')) {
            $this->_customThreadedCommentsCallback = true;
        }
    }
    
    /**
     * 评论回调函数
     * 
     * @access private
     * @return void
     */
    private function threadedCommentsCallback()
    {
        $singleCommentOptions = $this->_singleCommentOptions;
        if ($this->_customThreadedCommentsCallback) {
            return threadedComments($this, $singleCommentOptions);
        }
        
        $commentClass = '';
        if ($this->authorId) {
            if ($this->authorId == $this->ownerId) {
                $commentClass .= ' comment-by-author';
            } else {
                $commentClass .= ' comment-by-user';
            }
        }
?>
<li itemscope itemtype="http://schema.org/UserComments" id="<?php $this->theId(); ?>" class="comment-body<?php
    if ($this->levels > 0) {
        echo ' comment-child';
        $this->levelsAlt(' comment-level-odd', ' comment-level-even');
    } else {
        echo ' comment-parent';
    }
    $this->alt(' comment-odd', ' comment-even');
    echo $commentClass;
?>">
    <div class="comment-author" itemprop="creator" itemscope itemtype="http://schema.org/Person">
        <span itemprop="image"><?php $this->outputAvatar($singleCommentOptions->avatarSize, $singleCommentOptions->defaultAvatar); ?></span>
        <cite class="fn color-main" itemprop="name"><?php $singleCommentOptions->beforeAuthor();
        $this->author();
        $singleCommentOptions->afterAuthor(); ?></cite>
    </div>
    <div class="comment-reply">
        <?php $this->reply($singleCommentOptions->replyWord); ?>
    </div>
    <div class="comment-meta">
        <a href="<?php $this->permalink(); ?>"><time itemprop="commentTime" datetime="<?php echo date('c' , $this->created);?>"><?php $singleCommentOptions->beforeDate();
        $this->date($singleCommentOptions->dateFormat);
        $singleCommentOptions->afterDate(); ?></time></a>
        <?php if ('waiting' == $this->status) { ?>
        <em class="comment-awaiting-moderation"><?php $singleCommentOptions->commentStatus(); ?></em>
        <?php } ?>
    </div>
    <div class="comment-content" itemprop="commentText">
    <?php $this->text = $this->loadReplyTagByParentCommentId($this->realParent) . $this->text?>
        <?php echo $this->parseCommentContent($this->content); ?>
    </div>
    <?php if ($this->children) { ?>
    <div class="comment-children" itemprop="discusses">
        <?php $this->threadedComments(); ?>
    </div>
    <?php } ?>
</li>
<?php
    }

    private function outputAvatar($size = 32, $default = NULL) {
        if (Mirages::$options->commentsAvatar && !Mirages::$options->embedCommentOptions__disableQQAvatar && preg_match('/^(\d+)@qq.com$/i', $this->mail)) {
            if (NULL != Typecho_Router::get('mirages-api')) {
                $avatar = Typecho_Common::url(Typecho_Router::url('mirages-api', array("action" => "comment-avatar", "pathInfo" => $this->coid . "_" . $size . ".json")), Mirages::$options->index);
                echo '<img class="avatar" src="' . STATIC_PATH . "images/spinner.svg" . '" data-src="' . $avatar .  '" data-type="json" alt="' .
                    $this->author . '" width="' . $size . '" height="' . $size . '" />';
                return;
            }
        }

        $this->gravatar($size, $default);
    }

    /**
     * 调用gravatar输出用户头像
     *
     * @access public
     * @param integer $size 头像尺寸
     * @param string $default 默认输出头像
     * @return void
     */
    public function gravatar($size = 32, $default = NULL) {
        if ($this->options->commentsAvatar && 'comment' == $this->type) {
            $rating = $this->options->commentsAvatarRating;

            $this->pluginHandle(__CLASS__)->trigger($plugged)->gravatar($size, $rating, $default, $this);
            if (!$plugged) {
                $url = Typecho_Common::gravatarUrl($this->mail, $size, $rating, $default, true);
                echo '<img class="avatar" src="' . STATIC_PATH . "images/spinner.svg" . '" data-src="' . $url . '" alt="' .
                    $this->author . '" width="' . $size . '" height="' . $size . '" />';
            }
        }
    }

    /**
     * 输出文章发布日期
     *
     * @access public
     * @param string $format 日期格式
     * @return void
     */
    public function date($format = NULL) {
        echo Utils::formatDate($this->created, 'NATURAL');
    }

    private function loadReplyTagByParentCommentId($parentId) {
        if ($parentId == "0" || !array_key_exists($parentId, $this->_commentAuthors)) {
            return "";
        }
        $author = "@" . $this->_commentAuthors[$parentId];
        return '@@MIRAGES_SPAN_START@@' . $author . '@@MIRAGES_SPAN_END@@';
    }


    private function parseCommentContent($content) {
        $content = preg_replace('/@@MIRAGES_SPAN_START@@(.*?)@@MIRAGES_SPAN_END@@/i', '<span class="comment-reply-author">$1</span>', $content);
        return $content;
    }

    /**
     * 获取当前评论链接
     *
     * @access protected
     * @return string
     */
    protected function ___permalink()
    {

        if ($this->options->commentsPageBreak) {            
            $pageRow = array('permalink' => $this->parentContent['pathinfo'], 'commentPage' => $this->_currentPage);
            return Typecho_Router::url('comment_page',
                        $pageRow, $this->options->index) . '#' . $this->theId;
        }
        
        return $this->parentContent['permalink'] . '#' . $this->theId;
    }

    /**
     * 子评论
     *
     * @access protected
     * @return array
     */
    protected function ___children()
    {
        return $this->options->commentsThreaded && !$this->isTopLevel && isset($this->_threadedComments[$this->coid]) 
            ? $this->_threadedComments[$this->coid] : array();
    }

    /**
     * 是否到达顶层
     *
     * @access protected
     * @return boolean
     */
    protected function ___isTopLevel()
    {
//        return $this->levels > $this->options->commentsMaxNestingLevels - 2;
        return $this->levels > 0;
    }

    /**
     * 重载内容获取
     *
     * @access protected
     * @return void
     */
    protected function ___parentContent()
    {
        return $this->parameter->parentContent;
    }

    /**
     * 输出文章评论数
     *
     * @access public
     * @param string $string 评论数格式化数据
     * @return void
     */
    public function num()
    {
        $args = func_get_args();
        if (!$args) {
            $args[] = '%d';
        }

        $num = intval($this->_total);

        echo sprintf(isset($args[$num]) ? $args[$num] : array_pop($args), $num);
    }

    /**
     * 执行函数
     *
     * @access public
     * @return void
     */
    public function execute()
    {
        if (!$this->parameter->parentId) {
            return;
        }

        $commentsAuthor = Typecho_Cookie::get('__typecho_remember_author');
        $commentsMail = Typecho_Cookie::get('__typecho_remember_mail');
        $select = $this->select()->where('table.comments.cid = ?', $this->parameter->parentId)
        ->where('table.comments.status = ? OR (table.comments.author = ? AND table.comments.mail = ? AND table.comments.status = ?)', 'approved', $commentsAuthor, $commentsMail, 'waiting');
        $threadedSelect = NULL;
        
        if ($this->options->commentsShowCommentOnly) {
            $select->where('table.comments.type = ?', 'comment');
        }
        
        $select->order('table.comments.coid', 'ASC');
        $this->db->fetchAll($select, array($this, 'push'));
        
        /** 需要输出的评论列表 */
        $outputComments = array();
        
        /** 如果开启评论回复 */
        if ($this->options->commentsThreaded) {
        
            foreach ($this->stack as $coid => &$comment) {
                
                /** 取出父节点 */
                $parent = $comment['parent'];
            
                /** 如果存在父节点 */
                if (0 != $parent && isset($this->stack[$parent])) {
                
                    /** 如果当前节点深度大于最大深度, 则将其挂接在父节点上 */
//                    if ($comment['levels'] >= $this->options->commentsMaxNestingLevels) {
                    if ($comment['levels'] >= 2) {
                        $comment['levels'] = $this->stack[$parent]['levels'];
                        $parent = $this->stack[$parent]['parent'];     // 上上层节点
                        $comment['parent'] = $parent;
                    }
                
                    /** 计算子节点顺序 */
                    $comment['order'] = isset($this->_threadedComments[$parent]) 
                        ? count($this->_threadedComments[$parent]) + 1 : 1;
                
                    /** 如果是子节点 */
                    $this->_threadedComments[$parent][$coid] = $comment;
                } else {
                    $outputComments[$coid] = $comment;
                }
                
            }
        
            $this->stack = $outputComments;
        }
        
        /** 评论排序 */
        if ('DESC' == $this->options->commentsOrder) {
            $this->stack = array_reverse($this->stack, true);
//            $this->_threadedComments = array_map('array_reverse', $this->_threadedComments);
        }
        
        /** 评论总数 */
        $this->_total = count($this->stack);
        
        /** 对评论进行分页 */
        if ($this->options->commentsPageBreak) {
            if ('last' == $this->options->commentsPageDisplay && !$this->parameter->commentPage) {
                $this->_currentPage = ceil($this->_total / $this->options->commentsPageSize);
            } else {
                $this->_currentPage = $this->parameter->commentPage ? $this->parameter->commentPage : 1;
            }
            
            /** 截取评论 */
            $this->stack = array_slice($this->stack,
                ($this->_currentPage - 1) * $this->options->commentsPageSize, $this->options->commentsPageSize);
            
            /** 评论置位 */
            $this->row = current($this->stack);
            $this->length = count($this->stack);
        }
        
        reset($this->stack);
    }

    /**
     * 将每行的值压入堆栈
     *
     * @access public
     * @param array $value 每行的值
     * @return array
     */
    public function push(array $value)
    {
        $value = $this->filter($value);
        
        /** 计算深度 */
        if (0 != $value['parent'] && isset($this->stack[$value['parent']]['levels'])) {
            $value['levels'] = $this->stack[$value['parent']]['levels'] + 1;
        } else {
            $value['levels'] = 0;
        }

        $value['realParent'] = $value['parent'];

        /** 重载push函数,使用coid作为数组键值,便于索引 */
        $this->stack[$value['coid']] = $value;
        $this->_commentAuthors[$value['coid']] = $value['author'];
        $this->length ++;
        
        return $value;
    }

    /**
     * 输出分页
     *
     * @access public
     * @param string $prev 上一页文字
     * @param string $next 下一页文字
     * @param int $splitPage 分割范围
     * @param string $splitWord 分割字符
     * @param string $template 展现配置信息
     * @return void
     */
    public function pageNav($prev = '&laquo;', $next = '&raquo;', $splitPage = 3, $splitWord = '...', $template = '')
    {
        if ($this->options->commentsPageBreak && $this->_total > $this->options->commentsPageSize) {
            $default = array(
                'wrapTag'       =>  'ol',
                'wrapClass'     =>  'page-navigator'
            );

            if (is_string($template)) {
                parse_str($template, $config);
            } else {
                $config = $template;
            }

            $template = array_merge($default, $config);

            $pageRow = $this->parameter->parentContent;
            $pageRow['permalink'] = $pageRow['pathinfo'];

            $query = Typecho_Router::url('comment_page', $pageRow, $this->options->index);

            /** 使用盒状分页 */
            $nav = new Typecho_Widget_Helper_PageNavigator_Box($this->_total,
                $this->_currentPage, $this->options->commentsPageSize, $query);
            $nav->setPageHolder('commentPage');
            $nav->setAnchor('comments');
            
            echo '<' . $template['wrapTag'] . (empty($template['wrapClass']) 
                    ? '' : ' class="' . $template['wrapClass'] . '"') . '>';
            $nav->render($prev, $next, $splitPage, $splitWord, $template);
            echo '</' . $template['wrapTag'] . '>';
        }
    }

    /**
     * 递归输出评论
     *
     * @access protected
     * @return void
     */
    public function threadedComments()
    {
        $children = $this->children;
        if ($children) {
            //缓存变量便于还原
            $tmp = $this->row;
            $this->sequence ++;

            //在子评论之前输出
            echo $this->_singleCommentOptions->before;

            foreach ($children as $child) {
                $this->row = $child;
                $this->threadedCommentsCallback();
                $this->row = $tmp;
            }

            //在子评论之后输出
            echo $this->_singleCommentOptions->after;

            $this->sequence --;
        }
    }
    
    /**
     * 列出评论
     * 
     * @access private
     * @param mixed $singleCommentOptions 单个评论自定义选项
     * @return void
     */
    public function listComments($singleCommentOptions = NULL)
    {
        //初始化一些变量
        $this->_singleCommentOptions = Typecho_Config::factory($singleCommentOptions);
        $this->_singleCommentOptions->setDefault(array(
            'before'        =>  '<ol class="comment-list">',
            'after'         =>  '</ol>',
            'beforeAuthor'  =>  '',
            'afterAuthor'   =>  '',
            'beforeDate'    =>  '',
            'afterDate'     =>  '',
            'dateFormat'    =>  $this->options->commentDateFormat,
            'replyWord'     =>  _t('回复'),
            'commentStatus' =>  _t('您的评论正等待审核！'),
            'avatarSize'    =>  32,
            'defaultAvatar' =>  NULL
        ));
        $this->pluginHandle()->trigger($plugged)->listComments($this->_singleCommentOptions, $this);

        if (!$plugged) {
            if ($this->have()) { 
                echo $this->_singleCommentOptions->before;
            
                while ($this->next()) {
                    $this->threadedCommentsCallback();
                }
            
                echo $this->_singleCommentOptions->after;
            }
        }
    }
    
    /**
     * 重载alt函数,以适应多级评论
     * 
     * @access public
     * @return void
     */
    public function alt()
    {
        $args = func_get_args();
        $num = func_num_args();
        
        $sequence = $this->levels <= 0 ? $this->sequence : $this->order;
        
        $split = $sequence % $num;
        echo $args[(0 == $split ? $num : $split) -1];
    }

    /**
     * 根据深度余数输出
     *
     * @access public
     * @param string $param 需要输出的值
     * @return void
     */
    public function levelsAlt()
    {
        $args = func_get_args();
        $num = func_num_args();
        $split = $this->levels % $num;
        echo $args[(0 == $split ? $num : $split) -1];
    }
    
    /**
     * 评论回复链接
     * 
     * @access public
     * @param string $word 回复链接文字
     * @return void
     */
    public function reply($word = '')
    {
        if ($this->options->commentsThreaded && $this->parameter->allowComment) {
            $word = empty($word) ? _t('回复') : $word;
            $this->pluginHandle()->trigger($plugged)->reply($word, $this);
            
            if (!$plugged) {
                echo '<a href="' . substr($this->permalink, 0, - strlen($this->theId) - 1) . '?replyTo=' . $this->coid .
                    '#' . $this->parameter->respondId . '" rel="nofollow" onclick="return TypechoComment.reply(\'' .
                    $this->theId . '\', ' . $this->coid . ');">' . $word . '</a>';
            }
        }
    }
    
    /**
     * 取消评论回复链接
     * 
     * @access public
     * @param string $word 取消回复链接文字
     * @return void
     */
    public function cancelReply($word = '')
    {
        if ($this->options->commentsThreaded) {
            $word = empty($word) ? _t('取消回复') : $word;
            $this->pluginHandle()->trigger($plugged)->cancelReply($word, $this);
            
            if (!$plugged) {
                $replyId = $this->request->filter('int')->replyTo;
                echo '<a id="cancel-comment-reply-link" href="' . $this->parameter->parentContent['permalink'] . '#' . $this->parameter->respondId .
                '" rel="nofollow"' . ($replyId ? '' : ' style="display:none"') . ' onclick="return TypechoComment.cancelReply();">' . $word . '</a>';
            }
        }
    }

    /**
     * 获取当前评论内容
     *
     * @access protected
     * @return string
     */
    protected function ___content()
    {
        $text = $this->parentContent['hidden'] ? _t('内容被隐藏') : $this->text;

        $text = $this->pluginHandle(__CLASS__)->trigger($plugged)->content($text, $this);
        if (!$plugged) {
            $text = $this->options->commentsMarkdown ? $this->markdown($text)
                : $this->autoP($text);
        }

        $text = $this->pluginHandle(__CLASS__)->contentEx($text, $this);
        return Typecho_Common::stripTags($text, '<p><br>' . $this->options->commentsHTMLTagAllowed);
    }

    /**
     * markdown
     *
     * @param mixed $text
     * @access public
     * @return string
     */
    public function markdown($text)
    {
        $html = $this->pluginHandle(__CLASS__)->trigger($parsed)->markdown($text);

        if (!$parsed) {
            $text = preg_replace('/\#\[\s*(呵呵|哈哈|吐舌|太开心|笑眼|花心|小乖|乖|捂嘴笑|滑稽|你懂的|不高兴|怒|汗|黑线|泪|真棒|喷|惊哭|阴险|鄙视|酷|啊|狂汗|what|疑问|酸爽|呀咩爹|委屈|惊讶|睡觉|笑尿|挖鼻|吐|犀利|小红脸|懒得理|勉强|爱心|心碎|玫瑰|礼物|彩虹|太阳|星星月亮|钱币|茶杯|蛋糕|大拇指|胜利|haha|OK|沙发|手纸|香蕉|便便|药丸|红领巾|蜡烛|音乐|灯泡|开心|钱|咦|呼|冷|生气|弱|吐血)\s*\]/is',
                "@($1)", $text);
            $text = preg_replace('/\#\(\s*(高兴|小怒|脸红|内伤|装大款|赞一个|害羞|汗|吐血倒地|深思|不高兴|无语|亲亲|口水|尴尬|中指|想一想|哭泣|便便|献花|皱眉|傻笑|狂汗|吐|喷水|看不见|鼓掌|阴暗|长草|献黄瓜|邪恶|期待|得意|吐舌|喷血|无所谓|观察|暗地观察|肿包|中枪|大囧|呲牙|抠鼻|不说话|咽气|欢呼|锁眉|蜡烛|坐等|击掌|惊喜|喜极而泣|抽烟|不出所料|愤怒|无奈|黑线|投降|看热闹|扇耳光|小眼睛|中刀)\s*\)/is',
                "\\#($1)", $text);


            $html = Markdown::convert($text);
//            $html = $text;
        }

        return $html;
    }
}
