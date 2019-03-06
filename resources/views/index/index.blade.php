<html>
<meta charset="utf-8">
<head>
    <title>测试</title>
    <script src="js/echarts.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body>
<div style="width: 90%; text-align: center; margin-left: 50px;">
    <div class="container">
        <h1>抓包列表</h1>
    </div>
    <div style="margin-top: 20px">
        <form class="form-inline" style="text-align: left" action="/" method="get">
            <div class="form-group">
                <label class="sr-only">代码</label>
                <input type="text" class="form-control" name="code" placeholder="code | name" value="{{$code}}">
            </div>
            <button type="submit" class="btn btn-default">search</button>
        </form>
        <table class="table table-bordered">
            <tr>
                <th>名称</th>
                <th>编码</th>
                <th>当前价格</th>
                <th>综合得分</th>
                <th width="8%">当前评级</th>
                <th width="20%">短期</th>
                <th width="20%">中期</th>
                <th width="20%">长期</th>
                <th>更新时间</th>
                <th>操作</th>
            </tr>
            @foreach($list as $val)
                <tr>
                    <td>{{$val->name}}</td>
                    <td>{{$val->code}}</td>
                    <td>{{$val->price}}</td>
                    <td>{{$val->avg}}</td>
                    <td @if(in_array($val->current_rate, ['增持', '买入'])) style="color: red" @endif>
                        {{$val->current_rate}}
                        @if($val->star)
                            ({{$val->star}}星)
                        @endif
                    </td>
                    <td>{{$val->shot_target}}</td>
                    <td>{{$val->middle_target}}</td>
                    <td>{{$val->long_target}}</td>
                    <td>{{$val->updated_at}}</td>
                    <td>
                        <button class="btn btn-success" onclick="show({{$val->id}}, '{{$val->name}}', '{{$val->code}}')">显示</button>
                    </td>
                </tr>
            @endforeach
        </table>
        <div>
            {{ $list->links() }}
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">机构评级</h4>
                </div>
                <div class="modal-body">
                    <div id="myChart" style="width: 600px; height: 300px"></div>
                    <div style="font-size: 16px; margin-bottom: 10px;">
                        <span id="stock_name"></span>
                        <span id="stock_code"></span>
                    </div>
                    <table class="table table-bordered" id="showRate">
                        <tr>
                            <th>日期</th>
                            <th>机构</th>
                            <th>评级</th>
                            <th>上次评级</th>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>
</body>
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>
<script>
    function show(id, name, code) {
        $.ajax({
            type: 'POST',
            url: 'getRate',
            data: {stock_id: id},
            headers: {
                'X-CSRF-TOKEN': '{{csrf_token()}}'
            },
            success: function (data) {
                showRate(data.rateList);
                showChart(data.priceList);
            },
            dataType: 'json'
        });
        $('#stock_name').text(name);
        $('#stock_code').text(code);

        $('#myModal').modal();
    }

    function showRate(data)
    {
        var html = '';
        $('#showRate').find('tr:gt(0)').remove();
        $.each(data, function (i, item) {
            html += '<tr class="item">';
            html += '<td>' + item.rating_date + '</td>';
            html += '<td>' + item.organization + '</td>';
            html += '<td>' + item.rating + '</td>';
            html += '<td>' + item.last_rating + '</td>';
            html += '</tr>';
        });
        $('#showRate').append(html);
    }

    function showChart(data)
    {
        var myChart = echarts.init(document.getElementById('myChart'));
        var xdata = [], ydata = [];
        $.each(data, function(i, item){
            xdata.push(item.record_date);
            ydata.push(item.price);
        })
        console.log(xdata);

        // 指定图表的配置项和数据
        var option = {
            xAxis: {
                type: 'category',
                data: xdata
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                data: ydata,
                type: 'line'
            }]
        };

        // 使用刚指定的配置项和数据显示图表。
        myChart.setOption(option);
    }
</script>
</html>