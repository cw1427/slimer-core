<?php
/**
 * Author: Shawn Chen
 * Desc: The AdminLTE sideBar extension for the twig
 */
namespace Slimer\Html;

use Psr\Http\Message\ServerRequestInterface;
use Pimple\Container;

/**
 * slim/sidebar extension
 *
 * @author slim
 *
 * @see
 */
class SideBarExtension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    /**
     * @var container
     */
    
    protected $container;
    
    /**
     * @var \Slim\Csrf\Guard
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    
    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'sidebar_menu',
                [$this, 'SidebarMenuFunction'],
                ['is_safe' => ['html'], 'needs_environment' => true]
                ),
            new \Twig_SimpleFunction(
                'sidebar_toggle_button',
                [$this, 'ToggleButtonFunction'],
                ['is_safe' => ['html'], 'needs_environment' => true]
                ),
            new \Twig_SimpleFunction(
                'sidebar_collapse',
                [$this, 'SidebarCollapseFunction'],
                ['is_safe' => ['html'], 'needs_environment' => false]
                ),
            new \Twig_SimpleFunction(
                'isSubMenuActive',
                [$this, 'isSubMenuActive'],
                ['is_safe' => ['html'], 'needs_environment' => true]
                ),
            new \Twig_SimpleFunction(
                'getVersion',
                [$this, 'getVersion'],
                ['is_safe' => ['html'], 'needs_environment' => false]
                ),
            new \Twig_SimpleFunction(
                'getCommitId',
                [$this, 'getCommitId'],
                ['is_safe' => ['html'], 'needs_environment' => false]
                ),
            new \Twig_SimpleFunction(
                'isNeedIntro',
                [$this, 'isNeedIntro'],
                ['is_safe' => ['html'], 'needs_environment' => true]
                ),
            new \Twig_SimpleFunction(
                'getIntroduction',
                [$this, 'getIntroduction'],
                ['is_safe' => ['html'], 'needs_environment' => true]
                ),
            new \Twig_SimpleFunction(
                'isMenuVisible',
                [$this, 'isMenuVisible'],
                ['is_safe' => ['html'], 'needs_environment' => true]
                ),
        ];
    }
    
    function getFilters()
    {
        return [
            new \Twig_SimpleFilter('render_empty', [$this, 'RenderEmpty'],['is_safe' => ['html'], 'needs_environment' => false]),
        ];
    }
    
    public function RenderEmpty($value){
        if (isset($value) && ($value === '' || $value === ' ')){
            return "&nbsp";
        }else{
            return $value;
        }
    }
    
    
    
    public function SidebarMenuFunction(\Twig_Environment $environment, ServerRequestInterface $request)
    {
        
        /** @var SidebarMenuEvent $menuEvent */
        if ($this->container['session']->get('user') == null) return '';
        return $environment->render('adminlte/sidebar/menu.html.twig', ['menu' =>$this->container['config']('menu'),'request'=>$request]);
    }
    
    public function SidebarCollapseFunction($session)
    {
        if ($session->get('sbs_adminlte_sidebar_collapse') === 'true') {
            return 'sidebar-collapse';
        }
        return '';
    }
    
    public function ToggleButtonFunction(\Twig_Environment $environment)
    {
        /** @var RoutingExtension $routing */
        $template = '<a href="#" class="sidebar-toggle" data-toggle="push-menu" {{menuToggleIntro|raw}}><span class="sr-only">Toggle navigation</span></a>';
        
        try {
            $url = $this->container['router']->pathFor('sbs_adminlte_sidebar_collapse');
            return $environment
            ->createTemplate($template . '<script>
                    $(function () {
                        $(document).on("click", ".sidebar-toggle", function () {
                            event.preventDefault();
                            $.post("{{ url }}", {collapse: $("body").hasClass("sidebar-collapse")} );
                        });
                    });</script>')->render(['url' => $url,'menuToggleIntro'=>$this->getIntroduction($environment, 'menuToggleButton')]);
        } catch (\Exception $e) {
            return $template;
        }
    }
    
    
    public function isSubMenuActive(\Twig_Environment $environment, $children, $current_route)
    {
        $isActive = false;
        if ($children != null and sizeof($children)>0){
            foreach($children as $m){
                // recursion the sub menu to define it active.
                if (isset($m['children'])){
                    $isActive =  $this->isSubMenuActive($environment,$m['children'],$current_route);
                    if ($isActive) break;
                }else{
                    if (isset($m['route']) && strpos($m['route'],$current_route)){
                        $isActive = true;
                        break;
                    }
                    if (isset($m['routeName']) && strpos($m['routeName'],$current_route)){
                        $isActive = true;
                        break;
                    }
                }
            }
        }
        return $isActive;
    }
    
    public function getVersion()
    {
        $key=$this->container['config']('suit.version_key') ? $this->container['config']('suit.version_key') : "VERSION";
        return getenv($key) ?  getenv($key) : null;
    }
    
    public function getCommitId()
    {
        $key=$this->container['config']('suit.commitid_key') ? $this->container['config']('suit.commitid_key') : "COMMITID";
        return getenv($key) ?  getenv($key) : null;
    }
    
    public function isNeedIntro(\Twig_Environment $environment)
    {
        $currentUser = $this->container['session']->get('user');
        if ($currentUser == null || $this->container['config']('suit.intro_date') == null ) return false;
        if ($this->container['session']->get('introed') !== null) return !$this->container['session']->get('introed');
        if ($currentUser['lastLogin'] ==null || ($currentUser['lastLogin'] !=null && (strtotime($currentUser['lastLogin'])<= strtotime($this->container['config']('suit.intro_date'))))){
            return true;
        }
        return false;
    }
    
    public function getIntroduction(\Twig_Environment $environment, $introductionKey)
    {
        $introductions = $this->container['config']('suit.introductions');
        if ($introductions == null) return null;
        $out=[];
        foreach ($introductions[$introductionKey] as $k=>$v){
            array_push($out, "{$k}=\"{$v}\"");
        }
        return implode(" ", $out);
    }
    
    
    public function isMenuVisible(\Twig_Environment $environment,$item)
    {
        $menuVisibleByPerm = $this->container['config']('suit.menu_visible_by_perm');
        if (!isset($menuVisibleByPerm) || (isset($menuVisibleByPerm) && ($menuVisibleByPerm == false))) {
            return true;
        }
        //----check menu item route perm
        $routeName = isset($item['routeName']) ? $item['routeName'] : $item['route'];
        if ($routeName && $routeName != ''){
            if (strpos($routeName, "/") >= 0){
                $routeName = trim($routeName,"/");
                $routeName = str_replace("/", "-", $routeName);
            }
            $routes = \explode("-", $routeName);
            // bug fix, compatiable for index router
            if (sizeof($routes)==1){
                // default '/' route
                $routeConf = $this->container['config']('routes')['/'][\end($routes)];
            }else{
                $routeConf = $this->container['config']('routes')['/'.$routes[0]][\end($routes)];
            }
            if ($routeConf != null){
                if (isset($routeConf['perm'])){
                    $permGroup = $this->container['user']->get('perm_group');
                    if (isset($permGroup) && $permGroup != null){
                        $permGroupIds = [];
                        foreach ($permGroup as $group){
                            \array_push($permGroupIds,$group['ID']);
                        }
                        foreach ($routeConf['perm'] as $perm){
                            try{
                                if ($this->container['rbac']->check($perm,$permGroupIds)){
                                    return true;
                                }
                            } catch (\RbacPermissionNotFoundException $e){
                                $this->container['logger']->error("Permission {$perm} not found error");
                            }
                        }
                    }
                    return false;
                }else{
                    return true;
                }
            }else{
                return true;
            }
        }else if(isset($item['children'])){
            //----check children
            foreach ($item['children'] as $child){
                $childVisible = $this->isMenuVisible($environment,$child);
                if ($childVisible){
                    return true;
                }
            }
            return false;
        }else{
            return true;
        }
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Slimer/Html/SideBarExtension';
    }
}
