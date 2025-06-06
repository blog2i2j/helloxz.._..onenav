<?php include_once('header.php'); ?>
<?php include_once('left.php'); ?>

<div class="layui-body">
<!-- 内容主体区域 -->
<div class="layui-row content-body place-holder">

    <!-- 说明提示框 -->
    <div class="layui-col-lg12">
      <div class="page-msg">
        <ol>
          <li>仅 default2/5iux/heimdall/tushan2/webstack 支持自定义图标，其余主题均自动获取链接图标。</li>
          <li>分类的私有属性优先级高于链接的私有属性</li>
          <li>权重数字越大，链接排序越靠前</li>
        </ol>
      </div>
    </div>
    <!-- 说明提示框END -->

    <!-- 表单上面的按钮 -->
    <div class="lay-col-lg12">
    <form class="layui-form layui-form-pane" action="">
    <div class="layui-form-item">
    
        <div class="layui-inline">
            <div class="layui-input-inline">
                <select name="fid" lay-verify="" lay-search id = "fid">
                <option value="">请选择一个分类</option>
                <?php foreach( $categorys AS $category ){ ?>
                <option value="<?php echo $category['id'] ?>"><?php echo $category['name']; ?></option>
                <?php } ?>
                </select>   
            </div>
            <div class="layui-input-inline" style="width: 100px;">
                <button class="layui-btn" lay-submit lay-filter="screen_link">查询此分类下的链接</button>
            </div>
        </div>

        <div style="width:50px;display: inline-block;"></div>

        <!-- 顶部搜索 -->
        <div class="layui-inline" style="border-right:1px solid #ccc;">
            <div class="layui-input-inline">
                <input type="text" name="keyword" id="keyword" placeholder="请输入关键词" autocomplete="off" class="layui-input">
            </div>
            <div class="layui-input-inline" style="width: 70px;">
                <button class="layui-btn" lay-submit lay-filter="search_keyword">搜索</button>
            </div>
        </div>
        <!-- 顶部搜索END -->

        <!-- 批量检测 -->
        <div class="layui-inline">
            <div style="width: 90px;" class="layui-input-inline">
                <button class="layui-btn" lay-submit lay-filter="batch_check">批量检测</button>
            </div>
        </div>
        <!-- 批量检测END -->

        <!-- AI按钮 -->
        <div class="layui-inline" >
            <div class="layui-input-inline">
                <button class="layui-btn" lay-submit lay-filter="ai_search"><i style="color:#ff5722;font-weight:800;" class="layui-icon layui-icon-fire"></i> AI检索</button>
            </div>
        </div>
        <!-- AI按钮END -->

    </div>
    </form>
    </div>
    <!-- 表单上面的按钮END -->
    <div class="layui-col-lg12">
        <table id="link_list" lay-filter="mylink" lay-data="{id: 'mylink_reload'}"></table>
        <!-- 开启表格头部工具栏 -->
        <script type="text/html" id="linktool">
        <div class="layui-btn-container">
            <button class="layui-btn layui-btn-sm layui-btn-danger" lay-event="getCheckData">删除选中</button>
            <button class="layui-btn layui-btn-sm" lay-event="readmoredata">批量修改分类</button>
            <button class="layui-btn layui-btn-sm" lay-event="set_private">设为私有</button>
            <button class="layui-btn layui-btn-sm" lay-event="set_public">设为公有</button>
            <button class="layui-btn layui-btn-sm" lay-event="reset_query">重置查询</button>
            <button class="layui-btn layui-btn-sm" lay-event="addCategory">添加分类</button>
            <button class="layui-btn layui-btn-sm" lay-event="addLink">添加链接</button>
            <!-- <button class="layui-btn layui-btn-sm" lay-event="getCheckLength">获取选中数目</button>
            <button class="layui-btn layui-btn-sm" lay-event="isAll">验证是否全选</button> -->
        </div>
        </script>
        <!-- 开启表格头部工具栏END -->
    </div>
    <script type="text/html" id="link_operate">
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del" onclick = "">删除</a>
    </script>
    <!-- 表单下面的按钮 -->
    <button style="margin-top:16px;" class="layui-btn layui-btn-sm" lay-submit onclick = "export_link()">导出所有链接</button>
    <!-- 表单下面的按钮END -->
</div>
<!-- 内容主题区域END -->
</div>

<script>
layui.use(['table','form'], function(){
    var table = layui.table;
    var form = layui.form;

    // 编辑单行
    table.on('edit(mylink)',function(obj){
        var field = obj.field; // 得到字段
        var value = obj.value; // 得到修改后的值
        var data = obj.data; // 得到所在行所有键值

        // 获取到权重并判断是否合法
        let weight = data.weight;
        if( /^[-+]?\d*\.?\d+$/.test(weight) == false ) {
            layer.msg("权重必须为数字！",{icon:5});
            return obj.reedit();
        }
        // 获取到标题并判断是否合法
        let title = data.title.trim();
        if( title.length == 0 ) {
            layer.msg("标题不能为空！",{icon:5});
            return obj.reedit();
        }

        // 请求后端API
        $.ajax({
            url: '/index.php?c=api&method=edit_link_row',
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                // 请求成功后执行的代码
                if( response.code == 0 ) {
                    layer.msg("已修改！",{icon:1});
                }
                else{
                    layer.msg(response.msg,{icon:5});
                }
            },
            error: function(xhr, status, error) {
                // 请求出错时执行的代码
                console.log(error);
                layer.msg("修改失败！",{icon:5});
            }
        });
    });

    // 提交批量检测
    form.on('submit(batch_check)', function(data){
        let content = `
<ul>
<li>1. 此功能仅订阅用户可用！</li>
<li>2. 检测较为耗时，检测期间请勿关闭和刷新此页面！</li>
<li>3. 检测结果仅供参考，无法确保100%准确，具体以实际访问为准！</li>
</ul>
        `;
        // 弹出确认提示
        layer.confirm(content, {
            btn: ['开始检测','取消'],
            title: '即将对链接进行批量检测！',
            area: ['400px', 'auto']

        }, function(){
            // 关闭确认提示
            layer.closeAll('dialog');
            // 显示全局加载
            var index = layer.load(1);
            $.ajax({
                url: '/index.php?c=api&method=batch_check_links',
                type: 'GET',
                success: function(response) {
                    // 请求成功后执行的代码
                    if( response.code == 200 ) {
                        // 关闭全局加载
                        layer.close(index);
                        layer.msg("批量检测成功！",{icon:1});
                        // 2s后重新载入页面
                        setTimeout(function(){
                            location.reload();
                        },2000);
                    }
                    else{
                        layer.msg(response.msg,{icon:5});
                        // 关闭全局加载
                        layer.close(index);
                    }
                },
                error: function(xhr, status, error) {
                    layer.close(index);
                    // 请求出错时执行的代码
                    console.log(error);
                    layer.msg("批量检测失败！",{icon:5});
                }
            });
        }, function(){
            // 取消后执行的代码
        });
        return false;
    });

    // 点击AI
    form.on('submit(ai_search)', function(data){
        // alert("dsdsd");
        var index = layer.open({
            type: 2,
            title: false,
            shadeClose: true,
            maxmin: false, //开启最大化最小化按钮
            area: ['860px', '675px'],
            moveOut:true,
            content: '/index.php?theme=default2#/ai'
        });
        // 重新给对应层设定 width、top 等
        // layer.style(index, {
        //     'border-radius':'18px',
        // });
        // layer.style(index, {
        //     width: '1000px',
        //     top: '10px'
        // });
        return false;
    });

    // 提交搜索
    form.on('submit(search_keyword)', function(data){
        console.log(data.field);
        let keyword = data.field.keyword;

        if( keyword.length < 2 ) {
            layer.msg("关键词过短！",{icon:5});
            return false;
        }

        //渲染链接列表
        table.render({
            elem: '#link_list'
            ,height: 530
            ,url: 'index.php?c=api&method=global_search&keyword=' + keyword //数据接口
            ,method: 'post'
            ,toolbar: '#linktool'
            ,cols: [[ //表头
            {type:'checkbox'} //开启复选框
            ,{field: 'id', title: 'ID', width:80, sort: true}
            ,{field: 'font_icon', title: '图标', width:60, templet:function(d){
                if(d.font_icon == null || d.font_icon == "")
                {
                return '<img src="static/images/default.png" width="28" height="28">';
                }
                else
                {
                let random = getRandStr(4);
                let font_icon = d.font_icon;
                return `<img src="${font_icon}?random=${random}" width="28" height="28">`;
                }
            }}
            // ,{field: 'fid', title: '分类ID',sort:true, width:90}
            ,{field: 'category_name', title: '所属分类',sort:true,width:120}
            ,{field: 'url', title: 'URL',width:140,templet:function(d){
                var url = '<a target = "_blank" href = "' + d.url + '" title = "' + d.url + '">' + d.url + '</a>';
                return url;
            }}
            ,{field: 'title', title: '链接标题', width:140,edit: 'text'}
            ,{field: 'add_time', title: '添加时间', width:148, sort: true,templet:function(d){
                var add_time = timestampToTime(d.add_time);
                return add_time;
            }}
            ,{field: 'up_time', title: '修改时间', width:148,sort:true,templet:function(d){
                if(d.up_time == null){
                    return '';
                }
                else{
                    var up_time = timestampToTime(d.up_time);
                    return up_time;
                }
                
            }}
            ,{field: 'check_status', title: '状态', width: 80,sort:true,templet:function(d){
                let title = `检测时间：${d.last_checked_time}`; 
                if(d.check_status == 1) {
                    return `<span title="${title}" class="link-status-text layui-badge layui-bg-green">正常</span>`;
                }
                else if(d.check_status == 2) {
                    return `<span title="${title}" class="link-status-text layui-badge">异常</span>`;
                }
                else if(d.check_status == 3) {
                    return `<span title="${title}" class="link-status-text layui-badge layui-bg-cyan">未知</span>`;
                }
                else {
                    return `<span title="${title}" class="link-status-text layui-badge layui-bg-gray">未检测</span>`;
                }
            }}
            ,{field: 'property', title: '私有', width: 80, sort: true,templet: function(d){
                    if(d.property == 1) {
                        return '<button type="button" class="layui-btn layui-btn-xs">是</button>';
                    }
                    else {
                        return '<button type="button" class="layui-btn layui-btn-xs layui-btn-danger">否</button>';
                    }
            }}
            ,{field: 'weight', title: '权重', width: 75,sort:true,edit: 'text'}
            ,{field: 'click', title: '点击数',width:90,sort:true}
            ,{fixed: 'right', title:'操作', toolbar: '#link_operate'}
            ]]
        });
        // 渲染链接列表END

        return false;
    });

});

// 重置查询
function reset_query(){
    // 清空关键词
    $("#keyword").val("");
    layui.use(['table'], function(){
        var table = layui.table;

        //渲染链接列表
        table.render({
            elem: '#link_list'
            ,height: 530
            ,url: 'index.php?c=api&method=link_list'
            ,method: 'post'
            ,page: true //开启分页
            ,toolbar: '#linktool'
            ,cols: [[ //表头
            {type:'checkbox'} //开启复选框
            ,{field: 'id', title: 'ID', width:80, sort: true}
            ,{field: 'font_icon', title: '图标', width:60, templet:function(d){
                if(d.font_icon == null || d.font_icon == "")
                {
                return '<img src="static/images/default.png" width="28" height="28">';
                }
                else
                {
                let random = getRandStr(4);
                let font_icon = d.font_icon;
                return `<img src="${font_icon}?random=${random}" width="28" height="28">`;
                }
            }}
            // ,{field: 'fid', title: '分类ID',sort:true, width:90}
            ,{field: 'category_name', title: '所属分类',sort:true,width:120}
            ,{field: 'url', title: 'URL',width:140,templet:function(d){
                var url = '<a target = "_blank" href = "' + d.url + '" title = "' + d.url + '">' + d.url + '</a>';
                return url;
            }}
            ,{field: 'title', title: '链接标题', width:140,edit: 'text'}
            ,{field: 'add_time', title: '添加时间', width:148, sort: true,templet:function(d){
                var add_time = timestampToTime(d.add_time);
                return add_time;
            }}
            ,{field: 'up_time', title: '修改时间', width:148,sort:true,templet:function(d){
                if(d.up_time == null){
                    return '';
                }
                else{
                    var up_time = timestampToTime(d.up_time);
                    return up_time;
                }
                
            }} 
            ,{field: 'check_status', title: '状态', width: 80,sort:true,templet:function(d){
                let title = `检测时间：${d.last_checked_time}`; 
                if(d.check_status == 1) {
                    return `<span title="${title}" class="link-status-text layui-badge layui-bg-green">正常</span>`;
                }
                else if(d.check_status == 2) {
                    return `<span title="${title}" class="link-status-text layui-badge">异常</span>`;
                }
                else if(d.check_status == 3) {
                    return `<span title="${title}" class="link-status-text layui-badge layui-bg-cyan">未知</span>`;
                }
                else {
                    return `<span title="${title}" class="link-status-text layui-badge layui-bg-gray">未检测</span>`;
                }
            }}
            ,{field: 'property', title: '私有', width: 80, sort: true,templet: function(d){
                    if(d.property == 1) {
                        return '<button type="button" class="layui-btn layui-btn-xs">是</button>';
                    }
                    else {
                        return '<button type="button" class="layui-btn layui-btn-xs layui-btn-danger">否</button>';
                    }
            }}
            ,{field: 'weight', title: '权重', width: 75,sort:true,edit: 'text'}
            ,{field: 'click', title: '点击数',width:90,sort:true}
            ,{fixed: 'right', title:'操作', toolbar: '#link_operate'}
            ]]
        });
        // 渲染链接列表END
    })
}


    
</script>
<?php include_once('footer.php'); ?>