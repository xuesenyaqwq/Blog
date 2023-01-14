<?php
    // ===============你必须填写下面的必填参数才可以继续使用===============
    $_AFDIAN = array(
        'pageTitle' => '给我发电', // 网页标题
        'userName'  => 'SakuraSen', // 你的用户名，即你的主页地址 @ 后面的那部分，如 https://afdian.net/@MisaLiu，那么 MisaLiu 就是你的用户名
        'userId'    => '8dda570e047711ecb81d52540025c377', // 你的用户 ID，请前往  获取
        'token'     => 'uXhGDxk86eF5jqnNmsQfaTSvC37BcVMW'    // 你的 API Token，请前往 https://afdian.net/dashboard/dev 获取
    );
    // ===============你必须填写上面的必填参数才可以继续使用===============

    $currentPage = !empty($_POST['page']) ? $_POST['page'] : 1;

    $data = array();
    $data['user_id'] = $_AFDIAN['userId'];
    $data['params']  = json_encode(array('page' => $currentPage));
    $data['ts']      = time();
    $data['sign']    = SignAfdian($_AFDIAN['token'], $data['params'], $_AFDIAN['userId']);

    $result = HttpGet('https://afdian.net/api/open/query-sponsor?' . http_build_query($data));
    $result = json_decode($result, true);

    $donator['total']     = $result['data']['total_count'];
    $donator['totalPage'] = $result['data']['total_page'];
    $donator['list']      = $result['data']['list'];

    $donatorsHTML = '';
    for ($i = 0; $i < count($donator['list']); $i++) {
        $_donator = $donator['list'][$i];
        $_donator['last_sponsor'] = (empty(end($_donator['sponsor_plans'])['name']) ?
            (empty($_donator['current_plan']['name']) ? array('name' => '') : $_donator['current_plan']) :
            end($_donator['sponsor_plans']));
        
        $donatorsHTML .= '<div class="mdui-col-xs-12 mdui-col-md-6 mdui-m-b-2">
            <div class="mdui-card">
                <div class="mdui-card-header">
                    <img class="mdui-card-header-avatar" src="' . $_donator['user']['avatar'] . '" />
                    <div class="mdui-card-header-title">' . $_donator['user']['name'] .
                    '&nbsp;&nbsp;&nbsp;&nbsp;共' . $_donator['all_sum_amount'] . '元' . '</div>
                    <div class="mdui-card-header-subtitle">最后发电：' .
                    (empty($_donator['last_sponsor']['name']) ?
                        '暂无' :
                        $_donator['last_sponsor']['name'] . '&nbsp;&nbsp;' . $_donator['last_sponsor']['show_price'] . '元，于 ' . date('Y-m-d H:i:s', $_donator['last_pay_time'])) .
                    '</div>
                </div>' .
                (!empty($_donator['last_sponsor']['pi   c']) ? '
                    <div class="mdui-card-media">
                        <img src="' . $_donator['last_sponsor']['pic'] . '"/>
                    </div>' :
                    '') .
            '</div></div>';

    }

    $pageControlHTML = '<div class="mdui-row">
        <button onclick="switchPage(' . ($currentPage - 1) . ')" class="mdui-btn mdui-btm-raised mdui-ripple mdui-color-theme-accent mdui-float-left"' . ($currentPage == 1 ? ' disabled' : '') . '>
            <i class="mdui-icon material-icons">keyboard_arrow_left</i>
            上一页
        </button>
        <div class="mdui-btn-group -center">';
    for ($i = 0; $i < $donator['totalPage']; $i++) {
        $pageControlHTML .= '<button onclick="switchPage(' . ($i + 1) . ')" class="mdui-btn ' .
        ($i + 1 == $currentPage ? 'mdui-btn-active mdui-color-theme-accent' : 'mdui-text-color-theme-text') .
        '">' . ($i + 1) . '</button>';
    }
    $pageControlHTML .= '</div>
        <button onclick="switchPage(' . ($currentPage + 1) . ')" class="mdui-btn mdui-btm-raised mdui-ripple mdui-color-theme-accent mdui-float-right"' . ($donator['totalPage'] == 1 ? ' disabled' : '') . '>
            下一页
            <i class="mdui-icon material-icons">keyboard_arrow_right</i>
        </button>
    </div>';

    if (empty($_POST)) {
$html = <<< HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <meta name="viewport" content="width=device-width" />
        <link rel="stylesheet" href="./css/mdui.min.css" />
        <link rel="stylesheet" href="./css/main.css" />
        <script src="./js/mdui.min.js"></script>
        <title>${_AFDIAN['pageTitle']}</title>
    </head>
    <body class="mdui-appbar-with-toolbar mdui-theme-primary-blue-grey mdui-theme-accent-red mdui-theme-layout-auto">
        <header class="mdui-appbar mdui-appbar-fixed">
            <div class="mdui-progress mdui-hidden" style="position:absolute;top:0;width:100%" id="mdui_progress">
                <div class="mdui-progress-indeterminate" style="background-color:white"></div>
            </div>
            <div class="mdui-toolbar mdui-color-theme">
                <button class="mdui-btn mdui-btn-icon mdui-ripple" mdui-drawer="{target:'#drawer',swipe:true}"><i class="mdui-icon material-icons">menu</i></button>
                <a href="javascript:;" class="mdui-typo-headline">${_AFDIAN['pageTitle']}</a>
            </div>
        </header>

        <drawer class="mdui-drawer mdui-drawer-close" id="drawer">
            <div class="mdui-list">
                <a class="mdui-list-item mdui-ripple">
                    <i class="mdui-list-item-icon mdui-icon material-icons">home</i>
                    <div class="mdui-list-item-content">首页</div>
                </a>
            </div>
        </drawer>

        <main class="mdui-container mdui-typo">
            <h1 class="mdui-text-center">支持我，为我发电</h1>
            <iframe id="afdian_leaflet" class="mdui-center" src="https://afdian.net/leaflet?slug=${_AFDIAN['userName']}" scrolling="no" frameborder="0"></iframe>
            <div class="mdui-divider mdui-m-t-5"></div>
            <h2 class="mdui-text-center">感谢以下小伙伴的发电支持！</h2>
            
            <div class="mdui-m-b-2" id="afdian_sponsors">
                <div class="mdui-row">
                    ${donatorsHTML}
                </div>
                ${pageControlHTML}
            </div>
        </main>
        
    <script src='//cdn.badsen.cn/file/waline/waline.js'></script>
<link href='//cdn.badsen.cn/file/waline/waline.css' rel='stylesheet'/>
<div id="waline" class="waline-container"></div>
<style>
    .waline-container {
        background-color: var(--card-background);
        border-radius: var(--card-border-radius);
        box-shadow: var(--shadow-l1);
        padding: var(--card-padding);
        --waline-font-size: var(--article-font-size);
    }
    .waline-container .wl-count {
        color: var(--card-text-color-main);
    }
</style><script>
    
    Waline.init({"dark":"html[data-scheme=\"dark\"]","el":"#waline","emoji":["https://unpkg.com/@waline/emojis@1.0.1/weibo"],"lang":"zh-cn","locale":{"admin":"博主","placeholder":"说点什么吧~"},"pageview":"qwq","requiredMeta":["name","email","url"],"serverURL":"https://status.Badsen.cn"});
</script>

    

    <footer class="site-footer">
    <section class="copyright">
        &copy; 
        
            2021 - 
        
        2023 SakuraSenの个人博客
    </section>
    <section class="powerby">
<dev><p><a href="https://github.com/CaiJimmy/hugo-theme-stack" target="_blank"><i class="fa-brands fa-stack-overflow">Stack Theme</i></a>
&nbsp;&nbsp;| &nbsp;&nbsp;
<a href="https://afdian.net/a/SakuraSen" target="_blank"><i class="fa-solid fa-circle-dollar-to-slot"> 爱发电</i></a>
</p><p><a target="_blank"  href="https://www.beian.gov.cn/portal/registerSystemInfo?recordcode=41040302000085" style="display:inline-block;text-decoration:none;">豫公网安备 41040302000085号</a>&nbsp;&nbsp;<a href="https://beian.miit.gov.cn/" >豫ICP备2021036980号</a></p></div>
    </section>
</footer>


    
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">

    
    <div class="pswp__bg"></div>

    
    <div class="pswp__scroll-wrap">

        
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>

        
        <div class="pswp__ui pswp__ui--hidden">

            <div class="pswp__top-bar">

                

                <div class="pswp__counter"></div>

                <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>

                <button class="pswp__button pswp__button--share" title="Share"></button>

                <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>

                <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>

                
                
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                        <div class="pswp__preloader__cut">
                            <div class="pswp__preloader__donut"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div>
            </div>

            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
            </button>

            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
            </button>

            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>

        </div>

    </div>

</div><script 
                src="https://cdn.jsdelivr.net/npm/photoswipe@4.1.3/dist/photoswipe.min.js"integrity="sha256-ePwmChbbvXbsO02lbM3HoHbSHTHFAeChekF1xKJdleo="crossorigin="anonymous"
                defer
                >
            </script><script 
                src="https://cdn.jsdelivr.net/npm/photoswipe@4.1.3/dist/photoswipe-ui-default.min.js"integrity="sha256-UKkzOn/w1mBxRmLLGrSeyB4e1xbrp4xylgAWb3M42pU="crossorigin="anonymous"
                defer
                >
            </script><link 
                rel="stylesheet" 
                href="https://cdn.jsdelivr.net/npm/photoswipe@4.1.3/dist/default-skin/default-skin.min.css"crossorigin="anonymous"
            ><link 
                rel="stylesheet" 
                href="https://cdn.jsdelivr.net/npm/photoswipe@4.1.3/dist/photoswipe.min.css"crossorigin="anonymous"
            >

            </main>
        </div>
        <script 
                src="https://cdn.jsdelivr.net/npm/node-vibrant@3.1.6/dist/vibrant.min.js"integrity="sha256-awcR2jno4kI5X0zL8ex0vi2z&#43;KMkF24hUW8WePSA9HM="crossorigin="anonymous"
                
                >
            </script><script type="text/javascript" src="/ts/main.js" defer></script>
<script>
    (function () {
        const customFont = document.createElement('link');
        customFont.href = "https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap";

        customFont.type = "text/css";
        customFont.rel = "stylesheet";

        document.head.appendChild(customFont);
    }());
</script>

        <script src="./js/main.js"></script>
    </body>
</html>
HTML;

        echo $html;
    } else {
        $return = array();
        $return['code'] = $result['ec'];
        $return['msg']  = $result['em'];
        $return['html'] = (!empty($donatorsHTML) ? '<div class="mdui-row">' . $donatorsHTML . "</div>" . $pageControlHTML : '');

        echo json_encode($return);
    }

    function SignAfdian ($token, $params, $userId) {
        $sign = $token;
        $sign .= 'params' . $params;
        $sign .= 'ts' . time();
        $sign .= 'user_id' . $userId;
        return md5($sign, false);
    }

    function HttpGet ($url, $method = 'GET', $data = '', $contentType = '', $timeout = 10) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        if (!empty($contentType)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $contentType);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
?>