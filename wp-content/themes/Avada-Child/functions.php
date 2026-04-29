<?php
function avada_child_enqueue_styles() {
    wp_enqueue_style( 'avada-parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'avada-child-style', get_stylesheet_directory_uri() . '/style.css', array( 'avada-parent-style' ) );
}
add_action( 'wp_enqueue_scripts', 'avada_child_enqueue_styles' );

function project_b_force_utf8_blog_charset() {
    return 'UTF-8';
}
add_filter( 'pre_option_blog_charset', 'project_b_force_utf8_blog_charset' );
add_filter( 'option_blog_charset', 'project_b_force_utf8_blog_charset' );

function project_b_fix_utf8_charset_config() {
    @ini_set( 'default_charset', 'UTF-8' );

    $blog_charset = (string) get_option( 'blog_charset' );

    if ( 'UTF-8' !== strtoupper( $blog_charset ) ) {
        update_option( 'blog_charset', 'UTF-8' );
    }
}
add_action( 'init', 'project_b_fix_utf8_charset_config', 1 );

function project_b_send_utf8_content_type_header() {
    if ( is_admin() ) {
        return;
    }

    if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
        return;
    }

    if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
        return;
    }

    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        return;
    }

    if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
        return;
    }

    header( 'Content-Type: text/html; charset=UTF-8' );
}
add_action( 'send_headers', 'project_b_send_utf8_content_type_header', 0 );

$project_b_filebird_rebuild = get_stylesheet_directory() . '/inc/project-b-filebird-rebuild.php';

if ( file_exists( $project_b_filebird_rebuild ) ) {
	require_once $project_b_filebird_rebuild;
}

function project_b_menu_url( $type, $slug ) {
    if ( 'category' === $type ) {
        $term = get_category_by_slug( $slug );
        return $term ? get_category_link( $term ) : '#';
    }

    $page = get_page_by_path( $slug );
    return $page ? get_permalink( $page ) : '#';
}

function project_b_get_blog_menu_items() {
    return array(
        array(
            'label' => '전체',
            'type'  => 'category',
            'slug'  => 'blog',
            'url'   => project_b_menu_url( 'category', 'blog' ),
        ),
        array(
            'label' => '잡상노트',
            'type'  => 'category',
            'slug'  => 'blog-notes',
            'url'   => project_b_menu_url( 'category', 'blog-notes' ),
        ),
        array(
            'label' => '일상',
            'type'  => 'category',
            'slug'  => 'blog-daily',
            'url'   => project_b_menu_url( 'category', 'blog-daily' ),
        ),
        array(
            'label' => '캐나다 워홀',
            'type'  => 'category',
            'slug'  => 'canada-working-holiday',
            'url'   => project_b_menu_url( 'category', 'canada-working-holiday' ),
        ),
        array(
            'label' => 'Done List',
            'type'  => 'category',
            'slug'  => 'blog-done-list',
            'url'   => project_b_menu_url( 'category', 'blog-done-list' ),
        ),
    );
}

function project_b_is_privileged_user() {
    $user = wp_get_current_user();

    return $user instanceof WP_User
        && $user->exists()
        && 'wkddbsrud83' === $user->user_login;
}

function project_b_is_admin_viewer() {
    return current_user_can( 'manage_options' );
}

function project_b_can_view_member_menu() {
    if ( project_b_is_admin_viewer() ) {
        return true;
    }

    return project_b_user_has_named_role(
        wp_get_current_user(),
        array(
            'administrator',
            'admin',
            'um_custom_role_1',
            'um_custom_role_2',
            '앤오',
            '지인',
        )
    );
}

function project_b_normalize_access_label( $value ) {
    $value = trim( (string) $value );

    if ( function_exists( 'mb_strtolower' ) ) {
        $value = mb_strtolower( $value, 'UTF-8' );
    } else {
        $value = strtolower( $value );
    }

    return preg_replace( '/[\s\-_]+/u', '', $value );
}

function project_b_user_has_named_role( $user, $allowed_roles ) {
    if ( ! ( $user instanceof WP_User ) || ! $user->exists() ) {
        return false;
    }

    $normalized_allowed = array();

    foreach ( (array) $allowed_roles as $allowed_role ) {
        $normalized = project_b_normalize_access_label( $allowed_role );

        if ( '' !== $normalized ) {
            $normalized_allowed[ $normalized ] = true;
        }
    }

    if ( empty( $normalized_allowed ) ) {
        return false;
    }

    $wp_roles = function_exists( 'wp_roles' ) ? wp_roles() : null;

    foreach ( (array) $user->roles as $role_slug ) {
        $candidates   = array( $role_slug );
        $role_details = $wp_roles && isset( $wp_roles->roles[ $role_slug ] ) ? $wp_roles->roles[ $role_slug ] : null;

        if ( is_array( $role_details ) && ! empty( $role_details['name'] ) ) {
            $candidates[] = $role_details['name'];
        }

        foreach ( $candidates as $candidate ) {
            $normalized_candidate = project_b_normalize_access_label( $candidate );

            if ( '' !== $normalized_candidate && isset( $normalized_allowed[ $normalized_candidate ] ) ) {
                return true;
            }
        }
    }

    return false;
}

function project_b_can_view_melpin() {
    return project_b_is_admin_viewer();
}

function project_b_can_view_serial_content() {
    return project_b_is_admin_viewer();
}

function project_b_get_melpin_page_slug() {
    return 'melpin';
}

function project_b_get_melpin_category_slugs() {
    return array(
        'melpin-chat',
        'melpin-writing',
        'melpin-art',
        'melpin-picrew',
    );
}

function project_b_get_melpin_category_ids() {
    $ids = array();

    foreach ( project_b_get_melpin_category_slugs() as $slug ) {
        $term = get_category_by_slug( $slug );

        if ( $term instanceof WP_Term ) {
            $ids[] = (int) $term->term_id;

            $children = get_term_children( $term->term_id, 'category' );

            if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
                $ids = array_merge( $ids, array_map( 'intval', $children ) );
            }
        }
    }

    return array_values( array_unique( array_filter( $ids ) ) );
}

function project_b_filter_restricted_menu_items( $items ) {
    if ( project_b_can_view_melpin() || empty( $items ) || ! is_array( $items ) ) {
        return $items;
    }

    return array_values(
        array_filter(
            $items,
            function ( $item ) {
                return ! isset( $item['slug'] ) || project_b_get_melpin_page_slug() !== $item['slug'];
            }
        )
    );
}

function project_b_get_restricted_redirect_url() {
    return project_b_menu_url( 'page', 'oc-couples' );
}

function project_b_send_restricted_404() {
    global $wp_query;

    if ( $wp_query ) {
        $wp_query->set_404();
    }

    status_header( 404 );
    nocache_headers();

    $template = get_404_template();

    if ( $template ) {
        include $template;
    } else {
        include ABSPATH . WPINC . '/theme-compat/404.php';
    }

    exit;
}

function project_b_redirect_to_login_for_current_url() {
    $current_url = home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );

    wp_safe_redirect( wp_login_url( $current_url ) );
    exit;
}

function project_b_get_board_injected_post_slugs() {
    return array();
}

function project_b_get_blog_overlay_items() {
    return array_map(
        function ( $item ) {
            return array(
                'label' => $item['label'],
                'url'   => $item['url'],
            );
        },
        project_b_get_blog_menu_items()
    );
}

function project_b_filter_blog_menu_items_by_access( $items ) {
    if ( project_b_can_view_member_menu() ) {
        return $items;
    }

    return array_values(
        array_filter(
            (array) $items,
            function ( $item ) {
                return empty( $item['slug'] ) || 'blog-done-list' !== $item['slug'];
            }
        )
    );
}

function project_b_get_blog_board_terms() {
    $terms = array();
    foreach ( project_b_get_blog_menu_items() as $item ) {
        if ( empty( $item['slug'] ) || 'blog' === $item['slug'] ) {
            continue;
        }

        $slug = $item['slug'];
        $term = get_category_by_slug( $slug );

        if ( $term instanceof WP_Term ) {
            $terms[] = $term;
        }
    }

    return $terms;
}

function project_b_overlay_menu_payload() {
    $menu = array(
        array(
            'label' => 'BLOG',
            'url'   => project_b_menu_url( 'category', 'blog' ),
            'items' => project_b_filter_blog_menu_items_by_access( project_b_get_blog_overlay_items() ),
        ),
        array(
            'label' => 'REVIEW',
            'url'   => project_b_menu_url( 'category', 'review' ),
            'items' => array(
                array( 'label' => '맛집', 'url' => project_b_menu_url( 'category', 'food' ) ),
                array( 'label' => '음식', 'url' => project_b_menu_url( 'category', 'review-food-backup' ) ),
                array( 'label' => '영화 / 드라마', 'url' => project_b_menu_url( 'category', 'movie-drama' ) ),
                array( 'label' => '전시', 'url' => project_b_menu_url( 'category', 'exhibition' ) ),
                array( 'label' => '책', 'url' => project_b_menu_url( 'category', 'book' ) ),
                array( 'label' => '게임', 'url' => project_b_menu_url( 'page', 'game' ) ),
                array( 'label' => 'IT', 'url' => project_b_menu_url( 'category', 'it' ) ),
            ),
        ),
        array(
            'label' => 'TRAVEL',
            'url'   => project_b_menu_url( 'category', 'travel' ),
            'items' => array(
                array( 'label' => '2025', 'url' => project_b_menu_url( 'category', 'travel-2025' ) ),
                array( 'label' => '2024', 'url' => project_b_menu_url( 'category', 'travel-2024' ) ),
                array( 'label' => '2023', 'url' => project_b_menu_url( 'category', 'travel-2023' ) ),
            ),
        ),
        array(
            'label' => 'PROS',
            'url'   => project_b_menu_url( 'category', 'pros' ),
            'items' => array(
                array( 'label' => '커미션 / 리퀘', 'url' => project_b_menu_url( 'category', 'commission-request' ) ),
                array( 'label' => '개인작', 'url' => project_b_menu_url( 'category', 'personal-work' ) ),
            ),
        ),
        array(
            'label' => 'LOG',
            'url'   => project_b_menu_url( 'category', 'log' ),
            'items' => array(
                array( 'label' => '개인 연성', 'url' => project_b_menu_url( 'category', 'personal-creation-log' ) ),
                array( 'label' => '그림 연습', 'url' => project_b_menu_url( 'category', 'art-study' ) ),
                array( 'label' => '낙서', 'url' => project_b_menu_url( 'category', 'scribble' ) ),
                array( 'label' => '자캐 픽크루 백업', 'url' => project_b_menu_url( 'category', 'oc-picrew-backup' ) ),
                array( 'label' => '커미션', 'url' => project_b_menu_url( 'category', 'art-commission' ) ),
            ),
        ),
        array(
            'label' => 'OC',
            'url'   => project_b_menu_url( 'page', 'oc' ),
            'items' => array(
                array( 'label' => '자캐 커플', 'url' => project_b_menu_url( 'page', 'oc-couples' ) ),
                array( 'label' => '커뮤 로그 백업', 'url' => project_b_menu_url( 'category', 'commu-log-backup' ) ),
                array( 'label' => '그 외', 'url' => project_b_menu_url( 'category', 'oc-etc' ) ),
            ),
        ),
    );

    if ( project_b_can_view_member_menu() ) {
        return $menu;
    }

    return array_values(
        array_filter(
            $menu,
            function ( $item ) {
                return ! isset( $item['label'] ) || ! in_array( $item['label'], array( 'PROS', 'LOG', 'OC' ), true );
            }
        )
    );
}

function project_b_render_overlay_menu_override() {
    $menu = project_b_overlay_menu_payload();
    ?>
    <nav class="project-b-overlay-menu" aria-label="Project B menu">
        <button class="project-b-overlay-menu__close" type="button" aria-label="메뉴 닫기"></button>
        <form class="project-b-overlay-menu__search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <label class="project-b-overlay-menu__search-icon" aria-hidden="true">
                <span></span>
            </label>
            <input type="search" name="s" placeholder="검색어 입력" value="<?php echo esc_attr( get_search_query() ); ?>" />
        </form>
        <div class="project-b-overlay-menu__body">
            <div class="project-b-overlay-menu__primary">
                <?php foreach ( $menu as $index => $top_item ) : ?>
                    <section
                        class="project-b-overlay-menu__group"
                        data-menu-key="menu-<?php echo esc_attr( $index ); ?>"
                        data-menu-items="<?php echo esc_attr( wp_json_encode( array_values( $top_item['items'] ) ) ); ?>"
                    >
                        <a class="project-b-overlay-menu__top" href="<?php echo esc_url( $top_item['url'] ); ?>">
                            <?php echo esc_html( $top_item['label'] ); ?>
                        </a>
                    </section>
                <?php endforeach; ?>
            </div>
            <aside class="project-b-overlay-menu__panel" aria-live="polite">
                <ul class="project-b-overlay-menu__children"></ul>
            </aside>
        </div>
        <div class="project-b-overlay-menu__account-slot" hidden></div>
    </nav>
    <?php
}

function project_b_overlay_menu_assets() {
    wp_register_style( 'project-b-overlay-menu', false, array(), '1.0.0' );
    wp_enqueue_style( 'project-b-overlay-menu' );
    wp_add_inline_style(
        'project-b-overlay-menu',
        '.project-b-overlay-menu{--pb-accent:#e78645;display:none;position:relative;padding:18px 0 90px}.project-b-overlay-menu.is-mounted{display:block;margin-top:0}.project-b-overlay-menu__close{position:absolute;right:4px;top:-10px;width:48px;height:48px;border:0!important;background:transparent!important;box-shadow:none!important;cursor:pointer;z-index:20}.project-b-overlay-menu__close:before,.project-b-overlay-menu__close:after{content:"";position:absolute;left:7px;top:22px;width:40px;height:4px;background:#000;transform:rotate(45deg);transform-origin:center}.project-b-overlay-menu__close:after{transform:rotate(-45deg)}.project-b-overlay-menu__search{display:flex;align-items:center;gap:18px;margin:82px 0 66px;padding:0 0 23px;border-bottom:4px solid #111}.project-b-overlay-menu__search input{width:100%;border:0!important;outline:0!important;background:transparent!important;box-shadow:none!important;color:#111;font-size:32px;font-weight:900;line-height:1;letter-spacing:-.07em}.project-b-overlay-menu__search input::placeholder{color:#aaa;opacity:1}.project-b-overlay-menu__search-icon{position:relative;display:block;width:42px;height:42px;flex:0 0 42px;margin:0}.project-b-overlay-menu__search-icon:before{content:"";position:absolute;left:0;top:0;width:28px;height:28px;border:5px solid #000;border-radius:50%}.project-b-overlay-menu__search-icon:after{content:"";position:absolute;right:1px;bottom:2px;width:20px;height:5px;background:#000;transform:rotate(45deg);transform-origin:center}.project-b-overlay-menu__body{display:grid;grid-template-columns:300px minmax(240px,1fr);column-gap:22px;align-items:start}.project-b-overlay-menu__primary{position:relative}.project-b-overlay-menu__group{position:relative;margin:0 0 43px;min-height:58px}.project-b-overlay-menu__top{display:inline-block;color:#050505!important;text-decoration:none!important;font-size:36px;font-weight:900;line-height:1;letter-spacing:.18em;transition:color .16s ease}.project-b-overlay-menu__group.is-active .project-b-overlay-menu__top,.project-b-overlay-menu__group:hover .project-b-overlay-menu__top,.project-b-overlay-menu__group:focus-within .project-b-overlay-menu__top{color:var(--pb-accent)!important;text-decoration:none!important}.project-b-overlay-menu__panel{min-height:420px;padding-top:2px}.project-b-overlay-menu__children{display:flex;flex-direction:column;align-items:flex-start;gap:22px;list-style:none;margin:0;padding:0;width:100%}.project-b-overlay-menu__children:empty{display:block}.project-b-overlay-menu__children li{display:block;width:100%}.project-b-overlay-menu__children a{display:inline-block;color:#111!important;text-decoration:none!important;font-size:24px;font-weight:900;line-height:1.05;letter-spacing:-.06em;white-space:nowrap}.project-b-overlay-menu__children a:hover,.project-b-overlay-menu__children a:focus{color:var(--pb-accent)!important;text-decoration:none!important}.project-b-overlay-menu__account-slot[hidden]{display:none!important}@media (max-width:900px){.project-b-overlay-menu{padding-left:0;padding-right:0}.project-b-overlay-menu__search{margin-top:56px;margin-bottom:60px}.project-b-overlay-menu__body{grid-template-columns:260px minmax(220px,1fr);column-gap:12px}.project-b-overlay-menu__top{font-size:34px}.project-b-overlay-menu__children a{font-size:22px}}@media (max-width:760px){.project-b-overlay-menu__close{right:0;top:-6px}.project-b-overlay-menu__search{margin:56px 0 62px}.project-b-overlay-menu__search input{font-size:29px}.project-b-overlay-menu__body{display:block}.project-b-overlay-menu__top{font-size:32px;letter-spacing:.16em}.project-b-overlay-menu__group{min-height:auto;margin-bottom:36px}.project-b-overlay-menu__panel{display:none}.project-b-overlay-menu__group.is-active .project-b-overlay-menu__children-inline{display:flex!important;flex-direction:column;gap:14px;margin-top:18px;padding:0;list-style:none}.project-b-overlay-menu__group.is-active .project-b-overlay-menu__children-inline a{font-size:21px;font-weight:900;color:#111!important;text-decoration:none!important}}'
    );

    wp_register_script( 'project-b-overlay-menu', false, array(), '1.0.0', true );
    wp_enqueue_script( 'project-b-overlay-menu' );
    wp_add_inline_script(
        'project-b-overlay-menu',
        '(function(){function menuHTML(){return '. wp_json_encode( trim( preg_replace( '/\s+/', ' ', project_b_capture_overlay_menu() ) ) ) .';}var isClosing=false;var overlaySelectors=[".fusion-flyout-menu",".fusion-mobile-menu",".fusion-mobile-nav-holder",".fusion-header-has-flyout-menu-content",".awb-menu__overlay",".awb-off-canvas",".off-canvas-content"];function normalizeText(text){return(text||"").replace(/\s+/g," ").trim();}function restoreOverlayStyles(){if(isClosing){return;}overlaySelectors.forEach(function(sel){document.querySelectorAll(sel).forEach(function(el){if(el.dataset.projectBClosed==="1"){el.style.display="";el.style.visibility="";el.style.opacity="";delete el.dataset.projectBClosed;}});});document.querySelectorAll("[data-project-b-panel-closed=\"1\"]").forEach(function(el){el.style.display="";el.style.visibility="";el.style.opacity="";delete el.dataset.projectBPanelClosed;});}function isOldMenuContainer(el){var text=normalizeText(el.textContent||"");return text.indexOf("BLOG")>-1&&text.indexOf("REVIEW")>-1&&text.indexOf("TRAVEL")>-1&&text.indexOf("PROS")>-1&&text.indexOf("LOG")>-1&&text.indexOf("OC")===-1;}function findTarget(){var nodes=document.querySelectorAll("nav,ul,div,section");for(var i=0;i<nodes.length;i++){var el=nodes[i];if(!el.classList.contains("project-b-overlay-menu")&&isOldMenuContainer(el)){var r=el.getBoundingClientRect();if(r.width>150&&r.height>150&&r.left>window.innerWidth*0.35){return el;}}}return null;}function captureNativeAccountMarkup(target){var nodes=target.querySelectorAll("li,div,p,section,ul");var bestHTML="";var bestLength=1/0;for(var i=0;i<nodes.length;i++){var el=nodes[i];var text=normalizeText(el.textContent||"");var links=el.querySelectorAll("a");if(!links.length){continue;}if(text.length>80||links.length>4){continue;}var hasLogin=text==="로그인"||text.indexOf("로그인 ")===0||text.indexOf(" 로그인")>-1;var hasLogoutSettings=text.indexOf("로그아웃")>-1&&text.indexOf("설정")>-1;if(!hasLogin&&!hasLogoutSettings){continue;}if(text.length<bestLength){bestHTML=el.outerHTML;bestLength=text.length;}}return bestHTML;}function hardCloseOverlay(btn){isClosing=true;var menu=btn.closest(".project-b-overlay-menu");var panel=menu?menu.parentElement:null;if(panel){while(panel.parentElement&&panel.parentElement!==document.body){var text=normalizeText(panel.textContent||"");var rect=panel.getBoundingClientRect();if(text.indexOf("BLOG")>-1&&text.indexOf("REVIEW")>-1&&rect.width>200&&rect.height>200){break;}panel=panel.parentElement;}}overlaySelectors.forEach(function(sel){document.querySelectorAll(sel).forEach(function(el){el.classList.remove("fusion-is-active","active","open","is-open","awb-show","show");el.dataset.projectBClosed="1";el.style.visibility="hidden";el.style.opacity="0";});});if(panel){panel.dataset.projectBPanelClosed="1";panel.style.visibility="hidden";panel.style.opacity="0";panel.classList.remove("fusion-is-active","active","open","is-open","awb-show","show");}document.documentElement.classList.remove("fusion-flyout-menu-active","fusion-mobile-menu-design-flyout","awb-off-canvas-active","no-scroll");document.body.classList.remove("fusion-flyout-menu-active","fusion-mobile-menu-design-flyout","awb-off-canvas-active","no-scroll","overflow-hidden");document.body.style.overflow="";document.body.style.position="";document.querySelectorAll(".fusion-flyout-menu-toggle,.fusion-mobile-menu-icons a,[aria-expanded=true]").forEach(function(el){el.setAttribute("aria-expanded","false");});setTimeout(function(){isClosing=false;},220);}function closeOverlay(btn){hardCloseOverlay(btn);}function parseItems(group){var raw=group.getAttribute("data-menu-items");if(!raw){return [];}try{return JSON.parse(raw)||[];}catch(error){return [];}}function renderItemsMarkup(items){return items.map(function(item){var label=item&&item.label?String(item.label):"";var url=item&&item.url?String(item.url):"#";return "<li><a href=\\""+url.replace(/"/g,"&quot;")+"\\">"+label.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;")+"</a></li>";}).join("");}function ensureInlineList(group,items){var inline=group.querySelector(".project-b-overlay-menu__children-inline");if(!inline){inline=document.createElement("ul");inline.className="project-b-overlay-menu__children-inline";inline.hidden=true;group.appendChild(inline);}inline.innerHTML=renderItemsMarkup(items);return inline;}function bindInteractiveMenu(root){var groups=root.querySelectorAll(".project-b-overlay-menu__group");var panel=root.querySelector(".project-b-overlay-menu__children");if(!groups.length||!panel){return;}function clearActive(){groups.forEach(function(item){item.classList.remove("is-active");var inline=item.querySelector(".project-b-overlay-menu__children-inline");if(inline){inline.hidden=true;}});panel.innerHTML="";}function activate(group){groups.forEach(function(item){item.classList.toggle("is-active",item===group);});var items=parseItems(group);panel.innerHTML=renderItemsMarkup(items);groups.forEach(function(item){var inline=item.querySelector(".project-b-overlay-menu__children-inline");if(inline){inline.hidden=true;}});var activeInline=ensureInlineList(group,items);activeInline.hidden=false;}groups.forEach(function(group){group.addEventListener("mouseenter",function(){activate(group);});group.addEventListener("focusin",function(){activate(group);});group.addEventListener("click",function(){if(window.innerWidth<=760){activate(group);}});});root.addEventListener("mouseleave",function(){if(window.innerWidth>760){clearActive();}});clearActive();}function mount(){if(isClosing){return;}restoreOverlayStyles();var target=findTarget();if(!target){return;}if(target.dataset.projectBMenuMounted==="1"){return;}var nativeAccountMarkup=captureNativeAccountMarkup(target);target.dataset.projectBMenuMounted="1";target.innerHTML=menuHTML();var menu=target.querySelector(".project-b-overlay-menu");if(menu){menu.classList.add("is-mounted");bindInteractiveMenu(menu);var slot=menu.querySelector(".project-b-overlay-menu__account-slot");if(slot){if(nativeAccountMarkup){slot.hidden=false;slot.innerHTML=nativeAccountMarkup;}else{slot.remove();}}}var close=target.querySelector(".project-b-overlay-menu__close");if(close){close.addEventListener("click",function(event){event.preventDefault();event.stopPropagation();closeOverlay(close);});}}document.addEventListener("DOMContentLoaded",mount);document.addEventListener("click",function(event){if(event.target&&event.target.closest&&event.target.closest(".project-b-overlay-menu__close")){return;}restoreOverlayStyles();setTimeout(mount,80);setTimeout(mount,300);},true);document.addEventListener("keydown",function(event){if(event.key==="Escape"){var close=document.querySelector(".project-b-overlay-menu__close");if(close){close.click();}}});new MutationObserver(function(){if(!isClosing){mount();}}).observe(document.documentElement,{childList:true,subtree:true});})();'
    );
}
// Keep the theme's native overlay menu markup/styles active.
// The custom override was replacing the original menu container and
// breaking the existing login/settings area and spacing tweaks.

function project_b_capture_overlay_menu() {
    ob_start();
    project_b_render_overlay_menu_override();
    return ob_get_clean();
}

function project_b_force_child_single_template( $template ) {
    if ( is_admin() || ! is_single() || 'post' !== get_post_type() ) {
        return $template;
    }

    $child_single = get_stylesheet_directory() . '/single.php';

    if ( file_exists( $child_single ) ) {
        return $child_single;
    }

    return $template;
}
add_filter( 'template_include', 'project_b_force_child_single_template', 99 );

function project_b_get_member_only_category_slugs() {
    return array(
        'blog-done-list',
        'pros',
        'commission-request',
        'personal-work',
        'log',
        'personal-creation-log',
        'art-study',
        'scribble',
        'oc-picrew-backup',
        'art-commission',
        'commu-log-backup',
        'oc-etc',
        'oc',
        'kelden',
        'kelden-text',
        'kelden-art',
        'kelden-chat',
    );
}

function project_b_get_member_only_page_slugs() {
    return array(
        'oc',
        'oc-couples',
    );
}

function project_b_get_member_private_category_ids() {
    $ids = array();

    foreach ( project_b_get_member_only_category_slugs() as $slug ) {
        $term = get_category_by_slug( $slug );

        if ( ! ( $term instanceof WP_Term ) ) {
            continue;
        }

        $ids[] = (int) $term->term_id;

        $children = get_term_children( $term->term_id, 'category' );

        if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
            $ids = array_merge( $ids, array_map( 'intval', $children ) );
        }
    }

    return array_values( array_unique( array_filter( $ids ) ) );
}

function project_b_can_view_member_private_content() {
    return project_b_can_view_member_menu();
}

function project_b_enforce_member_only_sections() {
    if ( is_admin() || project_b_can_view_member_menu() ) {
        return;
    }

    if ( is_category( project_b_get_member_only_category_slugs() ) || is_page( project_b_get_member_only_page_slugs() ) ) {
        if ( is_user_logged_in() ) {
            project_b_send_restricted_404();
        }

        project_b_redirect_to_login_for_current_url();
    }
}
add_action( 'template_redirect', 'project_b_enforce_member_only_sections', 1 );

function project_b_enqueue_member_menu_guard() {
    if ( project_b_can_view_member_menu() ) {
        return;
    }

    wp_register_script( 'project-b-member-menu-guard', false, array(), '1.0.0', true );
    wp_enqueue_script( 'project-b-member-menu-guard' );
    wp_add_inline_script(
        'project-b-member-menu-guard',
        "(function(){function normalize(text){return (text||'').replace(/\\s+/g,' ').trim().toUpperCase();}function guard(){document.querySelectorAll('.pv-menu-item').forEach(function(item){var top=item.querySelector('.pv-menu-cat');if(!top){return;}var label=normalize(top.textContent);if(label==='PROS'||label==='LOG'||label==='OC'){item.remove();return;}if(label==='BLOG'){item.querySelectorAll('.pv-menu-sub a').forEach(function(link){if((link.getAttribute('href')||'').indexOf('blog-done-list')>-1||normalize(link.textContent)==='DONE LIST'){link.remove();}});}});}if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',guard);}else{guard();}window.addEventListener('load',guard);})();"
    );
}
add_action( 'wp_enqueue_scripts', 'project_b_enqueue_member_menu_guard', 30 );

function project_b_enable_post_excerpt() {
    add_post_type_support( 'post', 'excerpt' );
}
add_action( 'init', 'project_b_enable_post_excerpt' );

function project_b_deep_menu_landing_config() {
    $config = array(
        'game' => array(
            'title' => 'GAME',
            'items' => array(
                array( 'label' => '전체', 'type' => 'page', 'slug' => 'game' ),
                array( 'label' => 'FF14', 'type' => 'category', 'slug' => 'ff14' ),
                array( 'label' => 'Sims', 'type' => 'category', 'slug' => 'sims' ),
                array( 'label' => '기타', 'type' => 'category', 'slug' => 'game-etc' ),
            ),
        ),
        'serial' => array(
            'title' => 'SERIAL',
            'hide_tabs' => true,
            'items' => array(
                array( 'label' => '붉은바다', 'type' => 'category', 'slug' => 'crimson-ocean' ),
                array( 'label' => '미노스의 공장', 'type' => 'category', 'slug' => 'minos-factory' ),
                array( 'label' => '왕비실전', 'type' => 'category', 'slug' => 'iamqueen' ),
                array( 'label' => '샐러맨더', 'type' => 'category', 'slug' => 'salamander' ),
                array( 'label' => '새아버지 최면수업', 'type' => 'category', 'slug' => 'stepfather-hypnosis' ),
                array( 'label' => '기사단장 사용법: 마물 함락 편', 'type' => 'category', 'slug' => 'knight-captain-monster-siege' ),
                array( 'label' => '촉수형 난임 치료 연구소', 'type' => 'category', 'slug' => 'tentacle-fertility-lab' ),
            ),
        ),
        'oc' => array(
            'title' => 'OC',
            'items' => array(
                array( 'label' => '자캐 커플', 'type' => 'page', 'slug' => 'oc-couples' ),
                array( 'label' => '커뮤 로그 백업', 'type' => 'category', 'slug' => 'commu-log-backup' ),
                array( 'label' => '그 외', 'type' => 'category', 'slug' => 'oc-etc' ),
            ),
        ),
        'oc-couples' => array(
            'title' => 'OC COUPLES',
            'items' => array(
                array( 'label' => '전체', 'type' => 'page', 'slug' => 'oc-couples' ),
                array( 'label' => '언릴', 'type' => 'page', 'slug' => 'unril' ),
                array( 'label' => '멜핀', 'type' => 'page', 'slug' => 'melpin' ),
                array( 'label' => '켈든', 'type' => 'page', 'slug' => 'kelden' ),
            ),
        ),
        'unril' => array(
            'title' => '언릴',
            'items' => array(
                array( 'label' => '썰', 'type' => 'category', 'slug' => 'unril-chat' ),
                array( 'label' => '글', 'type' => 'category', 'slug' => 'unril-text' ),
                array( 'label' => '그림', 'type' => 'category', 'slug' => 'unril-art' ),
            ),
        ),
        'melpin' => array(
            'title' => '멜핀',
            'items' => array(
                array( 'label' => '썰', 'type' => 'category', 'slug' => 'melpin-chat' ),
                array( 'label' => '글', 'type' => 'category', 'slug' => 'melpin-writing' ),
                array( 'label' => '그림', 'type' => 'category', 'slug' => 'melpin-art' ),
                array( 'label' => '픽크루', 'type' => 'category', 'slug' => 'melpin-picrew' ),
            ),
        ),
        'kelden' => array(
            'title' => '켈든',
            'items' => array(
                array( 'label' => '썰', 'type' => 'category', 'slug' => 'kelden-chat' ),
                array( 'label' => '글', 'type' => 'category', 'slug' => 'kelden-text' ),
                array( 'label' => '그림', 'type' => 'category', 'slug' => 'kelden-art' ),
            ),
        ),
    );

    if ( ! project_b_can_view_melpin() ) {
        if ( isset( $config['oc-couples']['items'] ) ) {
            $config['oc-couples']['items'] = project_b_filter_restricted_menu_items( $config['oc-couples']['items'] );
        }

        unset( $config['melpin'] );
    }

    if ( isset( $config['pros']['items'] ) && is_array( $config['pros']['items'] ) ) {
        $config['pros']['items'][] = array(
            'label' => '연재',
            'type'  => 'page',
            'slug'  => 'serial',
        );
    }

    return $config;
}

function project_b_get_landing_resource( $type, $slug ) {
    if ( 'category' === $type ) {
        return get_category_by_slug( $slug );
    }

    return get_page_by_path( $slug );
}

function project_b_get_landing_resource_url( $type, $slug ) {
    $resource = project_b_get_landing_resource( $type, $slug );

    if ( ! $resource ) {
        return '#';
    }

    if ( 'category' === $type ) {
        return get_category_link( $resource );
    }

    return get_permalink( $resource );
}

if ( ! function_exists( 'project_b_get_post_preview_image_url' ) ) {
    function project_b_get_post_preview_image_url( $post_id, $size = 'large' ) {
        $post_id = (int) $post_id;

        if ( ! $post_id ) {
            return '';
        }

        $featured = get_the_post_thumbnail_url( $post_id, $size );

        if ( $featured ) {
            return $featured;
        }

        $content = (string) get_post_field( 'post_content', $post_id );

        if ( preg_match( '/wp-image-([0-9]+)/', $content, $id_match ) ) {
            $image_id = (int) $id_match[1];
            $image    = wp_get_attachment_image_url( $image_id, $size );

            if ( $image ) {
                return $image;
            }
        }

        if ( preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $content, $src_match ) ) {
            return esc_url_raw( $src_match[1] );
        }

        $attachments = get_attached_media( 'image', $post_id );

        if ( ! empty( $attachments ) ) {
            $attachment = reset( $attachments );
            $image      = wp_get_attachment_image_url( $attachment->ID, $size );

            if ( $image ) {
                return $image;
            }
        }

        return '';
    }
}

function project_b_get_landing_card_image_url( $type, $resource, $size = 'large' ) {
    if ( ! $resource ) {
        return '';
    }

    if ( 'category' === $type && $resource instanceof WP_Term ) {
        $custom_image = (string) get_term_meta( $resource->term_id, 'project_b_landing_card_image', true );

        if ( '' !== $custom_image ) {
            return esc_url_raw( $custom_image );
        }

        $serial_meta = project_b_get_serial_publication_meta( $resource->slug );

        if ( ! empty( $serial_meta['image'] ) ) {
            return esc_url_raw( $serial_meta['image'] );
        }

        $image_id = (int) get_term_meta( $resource->term_id, 'thumbnail_id', true );

        if ( $image_id ) {
            $image = wp_get_attachment_image_url( $image_id, $size );

            if ( $image ) {
                return $image;
            }
        }

        return '';
    }

    if ( $resource instanceof WP_Post ) {
        $custom_image = (string) get_post_meta( $resource->ID, 'project_b_landing_card_image', true );

        if ( '' !== $custom_image ) {
            return esc_url_raw( $custom_image );
        }

        return function_exists( 'project_b_get_post_preview_image_url' )
            ? project_b_get_post_preview_image_url( $resource->ID, $size )
            : get_the_post_thumbnail_url( $resource->ID, $size );
    }

    return '';
}

function project_b_get_serial_publication_map() {
    return array(
        'crimson-ocean' => array(
            'title'     => '붉은바다',
            'image'     => 'https://img.ridicdn.net/cover/2336003868/xxlarge?dpi=xxhdpi#1',
            'store_url' => 'https://ridibooks.com/books/2336003868?_s=search&_q=%EB%9D%BC%EA%B7%B8%EB%85%B8&_rdt_sid=SearchBookListWithTab',
        ),
        'minos-factory' => array(
            'title'     => '미노스의 공장',
            'image'     => 'https://img.ridicdn.net/cover/945067892/xxlarge?dpi=xxhdpi#1',
            'store_url' => 'https://ridibooks.com/books/945067892?_s=search&_q=%EB%9D%BC%EA%B7%B8%EB%85%B8&_rdt_sid=SearchBookListWithTab',
        ),
        'iamqueen' => array(
            'title'     => '왕비실전',
            'image'     => 'https://img.ridicdn.net/cover/1007088879/xxlarge?dpi=xxhdpi#1',
            'store_url' => 'https://ridibooks.com/books/1007088879?_s=search&_q=%EB%9D%BC%EA%B7%B8%EB%85%B8&_rdt_sid=SearchBookListWithTab',
        ),
        'salamander' => array(
            'title'     => '샐러맨더',
            'image'     => 'https://img.ridicdn.net/cover/809036386/xxlarge?dpi=xxhdpi#1',
            'store_url' => 'https://ridibooks.com/books/809036386?_s=search&_q=%EB%9D%BC%EA%B7%B8%EB%85%B8&_rdt_sid=SearchBookListWithTab',
        ),
        'stepfather-hypnosis' => array(
            'title'     => '새아버지 최면수업',
            'image'     => 'https://img.ridicdn.net/cover/2313052704/xxlarge?dpi=xxhdpi#1',
            'store_url' => 'https://ridibooks.com/books/2313052704?_s=search&_q=%EB%9D%BC%EA%B7%B8%EB%85%B8&_rdt_sid=SearchBookListWithTab',
        ),
        'knight-captain-monster-siege' => array(
            'title'      => "기사단장 사용법:<br>마물 함락 편",
            'plain_title'=> '기사단장 사용법: 마물 함락 편',
            'image'      => 'https://img.ridicdn.net/cover/2259016211/xxlarge?dpi=xxhdpi#1',
            'store_url'  => 'https://ridibooks.com/books/2259016211?_s=search&_q=%EB%9D%BC%EA%B7%B8%EB%85%B8&_rdt_sid=SearchBookListWithTab',
        ),
        'tentacle-fertility-lab' => array(
            'title'     => '촉수형 난임 치료 연구소',
            'image'     => 'https://img.ridicdn.net/cover/2259021011/xxlarge?dpi=xxhdpi#1',
            'store_url' => 'https://ridibooks.com/books/2259021011?_s=search&_q=%EB%9D%BC%EA%B7%B8%EB%85%B8&_rdt_sid=SearchBookListWithTab',
        ),
    );
}

function project_b_get_serial_publication_meta( $slug ) {
    $map = project_b_get_serial_publication_map();

    return isset( $map[ $slug ] ) ? $map[ $slug ] : array();
}

function project_b_get_serial_category_slugs() {
    $config = project_b_deep_menu_landing_config();
    $slugs  = array();

    if ( empty( $config['serial']['items'] ) || ! is_array( $config['serial']['items'] ) ) {
        return $slugs;
    }

    foreach ( $config['serial']['items'] as $item ) {
        if ( ! empty( $item['type'] ) && 'category' === $item['type'] && ! empty( $item['slug'] ) ) {
            $slugs[] = $item['slug'];
        }
    }

    return array_values( array_unique( array_filter( $slugs ) ) );
}

function project_b_get_serial_category_ids() {
    $ids = array();

    foreach ( project_b_get_serial_category_slugs() as $slug ) {
        $term = get_category_by_slug( $slug );

        if ( $term instanceof WP_Term ) {
            $ids[] = (int) $term->term_id;

            $children = get_term_children( $term->term_id, 'category' );

            if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
                $ids = array_merge( $ids, array_map( 'intval', $children ) );
            }
        }
    }

    return array_values( array_unique( array_filter( $ids ) ) );
}

function project_b_get_serial_store_url_for_term( $term ) {
    if ( ! ( $term instanceof WP_Term ) ) {
        return '';
    }

    $serial_meta = project_b_get_serial_publication_meta( $term->slug );

    if ( ! empty( $serial_meta['store_url'] ) ) {
        return esc_url_raw( $serial_meta['store_url'] );
    }

    $ancestor_ids = get_ancestors( $term->term_id, 'category' );
    array_unshift( $ancestor_ids, (int) $term->term_id );

    foreach ( $ancestor_ids as $term_id ) {
        $store_url = (string) get_term_meta( (int) $term_id, 'project_b_store_url', true );

        if ( '' !== $store_url ) {
            return esc_url_raw( $store_url );
        }
    }

    return '';
}

function project_b_get_serial_store_url_for_post( $post_id ) {
    $terms = get_the_category( $post_id );

    if ( empty( $terms ) ) {
        return '';
    }

    foreach ( $terms as $term ) {
        $store_url = project_b_get_serial_store_url_for_term( $term );

        if ( '' !== $store_url ) {
            return $store_url;
        }
    }

    return '';
}

function project_b_is_serial_term_protected( $term ) {
    if ( ! ( $term instanceof WP_Term ) ) {
        return false;
    }

    foreach ( project_b_get_serial_category_ids() as $serial_id ) {
        if ( (int) $term->term_id === (int) $serial_id || term_is_ancestor_of( $serial_id, $term->term_id, 'category' ) ) {
            return true;
        }
    }

    return false;
}

function project_b_force_landing_page_template( $template ) {
    if ( is_admin() || ! is_page() ) {
        return $template;
    }

    $config = project_b_deep_menu_landing_config();
    $slug   = get_post_field( 'post_name', get_queried_object_id() );

    if ( isset( $config[ $slug ] ) ) {
        $landing_template = get_stylesheet_directory() . '/page-deep-menu.php';
        if ( file_exists( $landing_template ) ) {
            return $landing_template;
        }
    }

    return $template;
}
add_filter( 'template_include', 'project_b_force_landing_page_template', 98 );

function project_b_get_board_category_root_ids() {
    $root_ids = array();
    $config   = project_b_deep_menu_landing_config();

    foreach ( $config as $entry ) {
        if ( empty( $entry['items'] ) || ! is_array( $entry['items'] ) ) {
            continue;
        }

        foreach ( $entry['items'] as $item ) {
            if ( empty( $item['type'] ) || 'category' !== $item['type'] || empty( $item['slug'] ) ) {
                continue;
            }

            $term = get_category_by_slug( $item['slug'] );

            if ( $term instanceof WP_Term ) {
                $root_ids[] = (int) $term->term_id;
            }
        }
    }

    $pros = get_category_by_slug( 'pros' );

    if ( $pros instanceof WP_Term ) {
        $root_ids[] = (int) $pros->term_id;
    }

    return array_values( array_unique( array_filter( $root_ids ) ) );
}

function project_b_force_board_category_template( $template ) {
    if ( is_admin() || ! is_category() ) {
        return $template;
    }

    $term = get_queried_object();

    if ( ! ( $term instanceof WP_Term ) || 'category' !== $term->taxonomy ) {
        return $template;
    }

    if ( 'blog-done-list' === $term->slug ) {
        $board_template = get_stylesheet_directory() . '/category.php';

        if ( file_exists( $board_template ) ) {
            return $board_template;
        }

        return $template;
    }

    $board_root_ids = project_b_get_board_category_root_ids();
    $should_use_board = false;

    foreach ( $board_root_ids as $root_id ) {
        if ( (int) $term->term_id === (int) $root_id || term_is_ancestor_of( $root_id, $term->term_id, 'category' ) ) {
            $should_use_board = true;
            break;
        }
    }

    if ( ! $should_use_board ) {
        return $template;
    }

    $board_template = get_stylesheet_directory() . '/category.php';

    if ( file_exists( $board_template ) ) {
        return $board_template;
    }

    return $template;
}
add_filter( 'template_include', 'project_b_force_board_category_template', 100 );

function project_b_protect_melpin_content() {
    if ( is_admin() || project_b_can_view_melpin() ) {
        return;
    }

    if ( is_page( project_b_get_melpin_page_slug() ) ) {
        project_b_send_restricted_404();
    }

    if ( is_category() ) {
        $term = get_queried_object();

        if ( $term instanceof WP_Term ) {
            $melpin_ids = project_b_get_melpin_category_ids();

            foreach ( $melpin_ids as $melpin_id ) {
                if ( (int) $term->term_id === $melpin_id || term_is_ancestor_of( $melpin_id, $term->term_id, 'category' ) ) {
                    project_b_send_restricted_404();
                }
            }
        }
    }

    if ( is_single() && 'post' === get_post_type() ) {
        $melpin_ids = project_b_get_melpin_category_ids();

        if ( ! empty( $melpin_ids ) && has_category( $melpin_ids, get_queried_object_id() ) ) {
            project_b_send_restricted_404();
        }
    }
}
add_action( 'template_redirect', 'project_b_protect_melpin_content', 1 );

function project_b_exclude_melpin_from_main_queries( $query ) {
    if ( is_admin() || ! $query->is_main_query() || project_b_can_view_melpin() ) {
        return;
    }

    if ( ! ( $query->is_home() || $query->is_search() || $query->is_archive() || $query->is_feed() ) ) {
        return;
    }

    $melpin_ids = project_b_get_melpin_category_ids();

    if ( empty( $melpin_ids ) ) {
        return;
    }

    $excluded = $query->get( 'category__not_in', array() );
    $excluded = is_array( $excluded ) ? $excluded : array();

    $query->set( 'category__not_in', array_values( array_unique( array_merge( $excluded, $melpin_ids ) ) ) );
}
add_action( 'pre_get_posts', 'project_b_exclude_melpin_from_main_queries' );

function project_b_redirect_published_serial_content() {
    if ( is_admin() || project_b_can_view_serial_content() ) {
        return;
    }

    if ( is_category() ) {
        $term = get_queried_object();

        if ( project_b_is_serial_term_protected( $term ) ) {
            if ( ! is_user_logged_in() ) {
                project_b_redirect_to_login_for_current_url();
            }

            project_b_send_restricted_404();
        }
    }

    if ( is_single() && 'post' === get_post_type() ) {
        $post_id     = get_queried_object_id();
        $serial_ids  = project_b_get_serial_category_ids();

        if ( ! empty( $serial_ids ) && has_category( $serial_ids, $post_id ) ) {
            if ( ! is_user_logged_in() ) {
                project_b_redirect_to_login_for_current_url();
            }

            project_b_send_restricted_404();
        }
    }
}
add_action( 'template_redirect', 'project_b_redirect_published_serial_content', 2 );

function project_b_register_excerpt_metabox() {
    add_meta_box(
        'project-b-excerpt-box',
        __( '한 줄 설명 (발췌)', 'Avada' ),
        'project_b_render_excerpt_metabox',
        'post',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'project_b_register_excerpt_metabox' );

function project_b_render_excerpt_metabox( $post ) {
    wp_nonce_field( 'project_b_save_excerpt_metabox', 'project_b_excerpt_nonce' );
    ?>
    <p style="margin:0 0 10px;">
        홈과 글 상세 상단에 들어갈 한 줄 설명입니다. 비워두면 표시되지 않습니다.
    </p>
    <textarea
        name="project_b_manual_excerpt"
        rows="3"
        style="width:100%;"
    ><?php echo esc_textarea( $post->post_excerpt ); ?></textarea>
    <?php
}

function project_b_save_excerpt_metabox( $post_id ) {
    if ( ! isset( $_POST['project_b_excerpt_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['project_b_excerpt_nonce'] ) ), 'project_b_save_excerpt_metabox' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['project_b_manual_excerpt'] ) ) {
        remove_action( 'save_post', 'project_b_save_excerpt_metabox' );
        wp_update_post(
            array(
                'ID'           => $post_id,
                'post_excerpt' => sanitize_textarea_field( wp_unslash( $_POST['project_b_manual_excerpt'] ) ),
            )
        );
        add_action( 'save_post', 'project_b_save_excerpt_metabox' );
    }
}
add_action( 'save_post', 'project_b_save_excerpt_metabox' );

function project_b_render_category_resource_fields( $term = null ) {
    $term_id      = $term instanceof WP_Term ? (int) $term->term_id : 0;
    $image_url    = $term_id ? (string) get_term_meta( $term_id, 'project_b_landing_card_image', true ) : '';
    $store_url    = $term_id ? (string) get_term_meta( $term_id, 'project_b_store_url', true ) : '';
    $help_message = 'SERIAL 같은 랜딩 카드에서 쓸 썸네일 URL입니다. 비워두면 기존 대표 이미지 또는 기본 이미지가 사용됩니다.';
    $store_help   = '비권한 사용자가 이 카테고리를 열 때 이동할 서점 링크입니다. SERIAL 카드 클릭 팝업도 이 주소를 사용합니다.';

    if ( $term instanceof WP_Term ) :
        ?>
        <tr class="form-field">
            <th scope="row"><label for="project_b_landing_card_image"><?php esc_html_e( '랜딩 카드 썸네일 URL', 'Avada' ); ?></label></th>
            <td>
                <input type="url" name="project_b_landing_card_image" id="project_b_landing_card_image" value="<?php echo esc_attr( $image_url ); ?>" class="regular-text" />
                <p class="description"><?php echo esc_html( $help_message ); ?></p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="project_b_store_url"><?php esc_html_e( '서점 링크 URL', 'Avada' ); ?></label></th>
            <td>
                <input type="url" name="project_b_store_url" id="project_b_store_url" value="<?php echo esc_attr( $store_url ); ?>" class="regular-text" />
                <p class="description"><?php echo esc_html( $store_help ); ?></p>
            </td>
        </tr>
        <?php
        return;
    endif;
    ?>
    <div class="form-field">
        <label for="project_b_landing_card_image"><?php esc_html_e( '랜딩 카드 썸네일 URL', 'Avada' ); ?></label>
        <input type="url" name="project_b_landing_card_image" id="project_b_landing_card_image" value="" />
        <p><?php echo esc_html( $help_message ); ?></p>
    </div>
    <div class="form-field">
        <label for="project_b_store_url"><?php esc_html_e( '서점 링크 URL', 'Avada' ); ?></label>
        <input type="url" name="project_b_store_url" id="project_b_store_url" value="" />
        <p><?php echo esc_html( $store_help ); ?></p>
    </div>
    <?php
}
add_action( 'category_add_form_fields', 'project_b_render_category_resource_fields' );
add_action( 'category_edit_form_fields', 'project_b_render_category_resource_fields' );

function project_b_save_category_resource_fields( $term_id ) {
    if ( ! current_user_can( 'manage_categories' ) ) {
        return;
    }

    if ( isset( $_POST['project_b_landing_card_image'] ) ) {
        update_term_meta(
            $term_id,
            'project_b_landing_card_image',
            esc_url_raw( wp_unslash( $_POST['project_b_landing_card_image'] ) )
        );
    }

    if ( isset( $_POST['project_b_store_url'] ) ) {
        update_term_meta(
            $term_id,
            'project_b_store_url',
            esc_url_raw( wp_unslash( $_POST['project_b_store_url'] ) )
        );
    }
}
add_action( 'created_category', 'project_b_save_category_resource_fields' );
add_action( 'edited_category', 'project_b_save_category_resource_fields' );
