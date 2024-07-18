const EBChart = {
    
    create: function(container, chartSettings){
        let chart, charConfig, baseConfig = {
            chart: {
                zoomType: 'xy',
                backgroundColor: null
            },
            credits: {
                enabled: false
            },
            title: {
                text: 'Chart title'
            },
            subtitle: {
                text: ''
            },
            xAxis: {
                type: 'datetime',
                title: {text: ''},
                gridLineWidth: 0,
            },
            yAxis: [{title:{text:''}},],
            series: [],
            plotOptions: {
                pie: {
                    dataLabels: {
                        formatter: function(){
                            return this.point.options.name ? this.point.name : moment(this.point.x).format(EB.workspace.DATE_FORMAT);
                        }
                        //format: '{point.name}: {point.y:.3f}'
                    },
                    tooltip: {
                        headerFormat: '',
                        pointFormat: '{point.name}: {point.y:.3f}'
                    },
                },
            }
        };

        function buildItemsList(){
            !chartSettings.items && (chartSettings.items = []);
            while (chart.series.length) {
                chart.series[0].remove();
            }
            var index = 0;
            chartSettings.items.forEach(item => {if(item){
                item.index = index++;
                item.zIndex = item.index;
                createChartItem(item);
            }});
            updateYAxis();
        }

        function createChartItem(item){
            var seri = {
                name: item.name,
                data: [],
            }
            item.type && (seri.type = item.type);
            item.color && (seri.color = '#' + item.color);
            item.valueSuffix && (seri.tooltip = {valueSuffix: item.valueSuffix});
            item.dashStyle && (seri.dashStyle = item.dashStyle);
            item.marker == 0 && (seri.marker = {enabled: false});
            item.marker == 2 && (seri.dataLabels = {enabled : true, style: {fontSize: '9px', color: '#666666'}, formatter : function() {return this.y}});
            item.zIndex && (seri.zIndex = item.zIndex);
            chart.addSeries(seri, false);
        }

        function updateYAxis(){
            let key = (chartSettings.yAxisTitle ? chartSettings.yAxisTitle : '') + '~~~' + (chartSettings.yAsixPosition ? chartSettings.yAsixPosition : '0'),
                opt = {opposite: chartSettings.yAsixPosition == 1, title: {text: chartSettings.yAxisTitle}},
                yAxis = [key];
        
            chartSettings.yAxisColor && $.extend(true, opt, {title:{style:{color: '#' + chartSettings.yAxisColor}},labels:{style:{color: '#' + chartSettings.yAxisColor}}});
            chart.yAxis[0].update(opt, false);
        
            chartSettings.items.forEach(item => {if(item){
                item.axisPosition == undefined && (item.axisPosition = 0);
                key = (item.axisTitle ? item.axisTitle : (chartSettings.yAxisTitle ? chartSettings.yAxisTitle : '')) + '~~~' + (item.axisPosition ? item.axisPosition : (chartSettings.yAsixPosition ? chartSettings.yAsixPosition : '0'));
                item.yAxis = yAxis.indexOf(key);
                item.yAxis < 0 && yAxis.push(key) && (item.yAxis = yAxis.length - 1);
                if(item.yAxis > 0){
                    opt = {opposite: item.axisPosition == 1, title: {text: item.axisTitle}};
                    //let color = item.yAxisColor ? item.yAxisColor : chartSettings.yAxisColor;
                    //color && (color = '#' + color) && $.extend(true, opt, {title: {style: {color: color}}, labels: {style: {color: color}}});
                    item.yAxisColor && $.extend(true, opt, {title: {style: {color: '#' + item.yAxisColor}}, labels: {style: {color: '#' + item.yAxisColor}}});
                    item.yAxis < chart.yAxis.length ? chart.yAxis[item.yAxis].update(opt, false) : chart.addAxis(opt, false);
                }
            }});
            chartSettings.items.forEach((item, index) => {if(item){
                chart.series[index].update({yAxis: item.yAxis}, false);
            }});
            while(chart.yAxis.length > yAxis.length && chart.yAxis.length > 1) chart.yAxis[chart.yAxis.length-1].remove(false);
            //yAxis.length == 0 && chart.yAxis[0].update({title: {text: ''}}, false);
        }

        typeof chartSettings == 'string' && chartSettings.startsWith('{') && (chartSettings = JSON.parse(chartSettings.replaceAll('\\"','"')));
        !chartSettings.title && (chartSettings.title = baseConfig.title.text);
        !chartSettings.timeRange && (chartSettings.timeRange = 10);
        !chartSettings.timeRangeUnit && (chartSettings.timeRangeUnit = 'day');
    
        charConfig = $.extend(true, {}, baseConfig);
        chartSettings.title && (charConfig.title.text = chartSettings.title);
        chartSettings.titleColor && (charConfig.title.style = {color: '#' + chartSettings.titleColor});
        chartSettings.subtitle == 'hidden' ? charConfig.title.text = '' : chartSettings.subtitle && (charConfig.subtitle.text = chartSettings.subtitle);
        chartSettings.subtitleColor && (charConfig.subtitle.style = {color: '#' + chartSettings.subtitleColor});
        chartSettings.yAxisTitle == 'hidden' && (charConfig.yAxis[0].visible = false);
        chartSettings.xAxisTitle == 'hidden' ? charConfig.xAxis.visible = false : chartSettings.xAxisTitle && (charConfig.xAxis.title.text = chartSettings.xAxisTitle);
        chartSettings.xAxisColor && $.extend(true, charConfig.xAxis, {title:{style:{color: '#' + chartSettings.xAxisColor}},labels:{style:{color: '#' + chartSettings.xAxisColor}}});// (charConfig.xAxis.title.style = {color: '#' + chartSettings.xAxisColor});
        chartSettings.verticalLines && (charConfig.xAxis.gridLineWidth = 1);
        chartSettings.stacking && (charConfig.plotOptions.column = {stacking: 'normal'});
        chartSettings.legend == false && (charConfig.legend = {enabled: false});
    
        $.extend(true, charConfig.yAxis[0], {opposite: chartSettings.yAsixPosition == 1, title: {text: chartSettings.yAxisTitle}});
        chartSettings.yAxisColor && $.extend(true, charConfig.yAxis[0], {title:{style:{color: '#' + chartSettings.yAxisColor}},labels:{style:{color: '#' + chartSettings.yAxisColor}}});
    
        chart = Highcharts.chart(container, charConfig);
        buildItemsList();
        chart.redraw();

        return chart;
    },
}