<html><head>
<style>
    .formdiv { width: 450px; height: 150px; margin:0 auto; background-color: lightgray; font-size:15; border: 1px solid gray; margin-bottom: -50px; }
    
    .tablediv { width: 1200px; margin:0 auto; }
    
    table { border: 1px solid rgb(200, 200, 200); border-collapse: collapse; font-family: sans-serif; font-size: 13;}
    
    .leftcol { vertical-align: center; border: 1px solid rgb(200, 200, 200); border-collapse: collapse; background-color: rgb(220, 220, 220); width: 400px; text-align: left; }
    
    .rightcol { vertical-align: center; border: 1px solid #ddd; border-collapse: collapse; background-color: rgb(240, 240, 240); width: 800px; text-align: center; }
    
    a { color: blue; text-decoration: none; cursor: pointer}
    a:hover { color: black; text-decoration: none; }
    
    .chartdiv { width: 1200px; margin:0 auto; border: 1px solid rgb(200, 200, 200); height: 500px; }
    
    .image { margin:0 auto; text-align: center; padding-top: 7px;}
    
    .newsbutton { background-color: white; border: none;; text-align: center; text-decoration: none; color: darkgray; font-size: 13 }
    
    .newstable{ display: none; font-family: sans-serif; font-size: 13; width: 1200px; margin:0 auto; border-collapse:collapse; background-color: rgb(240, 240, 240); }
    
    .newsrow{ width: 1200px;padding: 9px; border: 1px solid rgb(200, 200, 200); }
</style>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
</head>

<div class="formdiv">
    <form name="myForm" method="POST" action="">
        <span style="padding-left: 30%; font-size:32;">
            <i>Stock Search</i>
        </span>
        <div style="padding-left: 10px; padding-right: 10px; padding-top: 0px">
            <hr>
        </div>
        <br>
        &nbsp;&nbsp;
        Enter Stock Ticker Symbol:*&nbsp;
        <input type="text" name="tickerSymbol" style="margin-bottom: 5px;" value="<?php if(isset($_POST["search"])) echo $_POST["tickerSymbol"]?>"><br>
        <span style="padding-left: 192px;">
            <input type="submit" name="search" value=" Search " onclick="Submit(this.form)">&nbsp;
            <input type="button" name="clear" value=" Clear " onclick="Clear(this.form)"><br>
        </span>
        &nbsp;&nbsp;
        <i>* - Mandatory fields.</i>
    </form>
</div>

<script language="JavaScript">
    function Submit(form)
    {
        if(form.tickerSymbol.value.length==0)
            {
                window.alert("Please enter a symbol");
                exit;
            }
    }
    
    function Clear(form)
    {
        form.tickerSymbol.value="";
        
        var el1 = document.getElementsByClassName("tablediv");
        if(el1 != null)
        {
            el1[0].style.display='none';
        }
        var el2 = document.getElementsByClassName("chartdiv");
        if(el2 != null)
        {
            el2[0].style.display='none';
        }
        var el3 = document.getElementsByClassName("newsdiv");
        if(el3 != null)
        {
            el3[0].style.display='none';
        }
    }
    
    function ToggleNews()
    {
        var newsTable=document.getElementsByClassName("newstable")[0];
        
        if(newsTable.style.display==='none' || newsTable.style.display==='')
        {
            document.getElementsByClassName("image")[0].innerHTML="<button class=\"newsbutton\" onClick='ToggleNews()'>click to hide stock news<br><img src=" + "\"http://cs-server.usc.edu:45678/hw/hw6/images/Gray_Arrow_Up.png\"" + "height=30px></button>";
    
            newsTable.style.display='block';
        }
        
        else
        {
            document.getElementsByClassName("image")[0].innerHTML="<button class=\"newsbutton\" onClick='ToggleNews()'>click to show stock news<br><img src=" + "\"http://cs-server.usc.edu:45678/hw/hw6/images/Gray_Arrow_Down.png\"" + "height=30px></button>";
    
            newsTable.style.display='none';
        }   
    }
    
</script>

<div class="tablediv">
<br><br><br><br>
<?php
if(isset($_POST["search"]) && $_POST["tickerSymbol"] != "")
{
    $symbol=$_POST["tickerSymbol"];
    $tableUrl="https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=" . $symbol . "&apikey=JXIE9QZNR7RYE9KA&outputsize=full";
    
    $headers=get_headers($tableUrl);
    $responseCode= substr($headers[0], 9, 3);
    if($responseCode != "200")
    {
        echo "<table>";
        echo "<tr>";
        echo "<td class=\"leftcol\"><b>Error</b></td>";
        echo "<td class=\"rightcol\">Error: Received response code " . $responseCode . "</td>";
        echo "</tr>";
        echo "</table>";
        exit();
    }
    
    $content=file_get_contents($tableUrl);
    $jsonContent = json_decode($content);
    
    $metadataKey="Meta Data";
    $timeSeriesKey="Time Series (Daily)";
    
    $symbolKey="2. Symbol";
    $timeKey="3. Last Refreshed";
    $openKey="1. open";
    $closeKey="4. close";
    $volKey="5. volume";
    $lowKey="3. low";
    $highKey="2. high";
    
    $symbol="";
    $timestamp="";
    $open="";
    $close="";
    $vol="";
    $prevClose="";
    $range="";
    
    if(property_exists($jsonContent, "Error Message"))
    {
        echo "<table>";
        echo "<tr>";
        echo "<td class=\"leftcol\"><b>Error</b></td>";
        echo "<td class=\"rightcol\">Error: NO record has been found, please enter a valid symbol</td>";
        echo "</tr>";
        echo "</table><br>";
        exit();
    }
    
    if($jsonContent->$metadataKey != null)
    {
        $symbol=$jsonContent->$metadataKey->$symbolKey;
        //$symbol=strtoupper($symbol);
        $timestamp=$jsonContent->$metadataKey->$timeKey;
    }
    
    if($jsonContent->$timeSeriesKey != null)
    {
        $count=0;
        foreach($jsonContent->$timeSeriesKey as $key=>$value)
        {
            if($count==0)
            {
                $open=$value->$openKey;
                $close=$value->$closeKey;
                $vol=$value->$volKey;
                $high=$value->$highKey;
                $low=$value->$lowKey;
                $range=$low . "-" . $high;
                $count=$count+1;
            }
            else if($count==1)
            {
                $prevClose=$value->$closeKey;
                $count=$count+1;
            }
            else if($count==2)
            {
                break;
            } 
        }
    }
       
    echo "<table>";
    
    echo "<tr>";
    echo "<td class=\"leftcol\"><b>Stock Ticker Symbol</b></td>";
    echo "<td class=\"rightcol\">" . $symbol . "</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td class=\"leftcol\"><b>Close</b></td>";
    echo "<td class=\"rightcol\">" . $close . "</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td class=\"leftcol\"><b>Open</b></td>";
    echo "<td class=\"rightcol\">" . $open . "</td>";
    echo "</tr>";
    
    echo "<td class=\"leftcol\"><b>Previous Close</b></td>";
    echo "<td class=\"rightcol\">" . $prevClose . "</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td class=\"leftcol\"><b>Change</b></td>";
    $change=$close-$prevClose;
    $change=number_format((float)$change, 2, '.', '');
    if($change>=0)
    {
        echo "<td class=\"rightcol\">" . $change . " <img src=http://cs-server.usc.edu:45678/hw/hw6/images/Green_Arrow_Up.png width=14px height=14px;></td>";
    }
    else
    {
        echo "<td class=\"rightcol\">" . $change . " <img src=http://cs-server.usc.edu:45678/hw/hw6/images/Red_Arrow_Down.png width=14px height=14px;></td>";
    }
    echo "</tr>";
    
    echo "<tr>";
    echo "<td class=\"leftcol\"><b>Change Percent</b></td>";
    $percentage=($change/$prevClose)*100;
    $percentage=round((float)$percentage, 2);
    if($change>=0)
    {
        echo "<td class=\"rightcol\">" . $percentage . "% <img src=http://cs-server.usc.edu:45678/hw/hw6/images/Green_Arrow_Up.png width=14px height=14px;></td>";
    }
    else
    {
        echo "<td class=\"rightcol\">" . $percentage . "% <img src=http://cs-server.usc.edu:45678/hw/hw6/images/Red_Arrow_Down.png width=14px height=14px;></td>";
    }
    echo "</tr>";
    
    echo "<tr>";
    echo "<td class=\"leftcol\"><b>Day's Range</b></td>";
    echo "<td class=\"rightcol\">" . $range . "</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td class=\"leftcol\"><b>Volume</b></td>";
    $vol=number_format($vol);
    echo "<td class=\"rightcol\">" . $vol . "</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td class=\"leftcol\"><b>Timestamp</b></td>";
    date_default_timezone_set("America/New_York");
    $fullDate=date_create($timestamp);
    $formattedDate=date_format($fullDate,"Y-m-d");
    echo "<td class=\"rightcol\">" . $formattedDate . "</td>";
    echo "</tr>";

    echo "<td class=\"leftcol\"><b>Indicators</b></td>";
    $spaces="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<td class=\"rightcol\"><a onclick=DisplayPriceVolumeChart()>Price</a>" . $spaces . "<a onclick=DisplayIndicatorChart1('SMA')>SMA</a>" . $spaces . "<a onclick=DisplayIndicatorChart1('EMA')>EMA</a>" . $spaces . "<a onclick=DisplayIndicatorChart2('STOCH')>STOCH</a>" . $spaces . "<a onclick=DisplayIndicatorChart1('RSI')>RSI</a>" . $spaces . "<a onclick=DisplayIndicatorChart1('ADX')>ADX</a>" . $spaces . "<a onclick=DisplayIndicatorChart1('CCI')>CCI</a>" . $spaces . "<a onclick=DisplayIndicatorChart3('BBANDS')>BBANDS</a>" . $spaces . "<a onclick=DisplayIndicatorChart3('MACD')>MACD</a></td>";
    echo "</tr>";
    
    echo "</table>";
    echo "</div><br><br>";
    
    echo "<div class=\"chartdiv\" id=\"chartid\">";

    $volArray=[];
    $dateArray=[];
    $priceArray=[];
    
    $today = new DateTime($formattedDate);
    
    foreach($jsonContent->$timeSeriesKey as $key=>$value)
    {
        $date=new DateTime($key);
        $diff=$today->diff($date);
        if($diff->days>181)
        {
            break;
        }
        $dateArray[]="\"" . $date->format('m/d') . "\""; 
        
        $price=$value->$closeKey;
        $price=round((float)$price, 2);
        $priceArray[]=$price;
        
        $vol=$value->$volKey;
        $vol=$vol/1000000 ;
        $volArray[]=$vol;   
    }
    
    $revDateArray=array_reverse($dateArray);
    $revVolArray=array_reverse($volArray);
    $revPriceArray=array_reverse($priceArray);
    $maxVol=max($volArray);
    $minPrice=min($priceArray); 
    
    $dateCount=sizeof($revDateArray);
    if(($dateCount-1)%5 !=0)
    {
        $ind=($dateCount-1)%5;
        for($j=0;$j<$ind;$j++)
        {
            array_shift($revDateArray);
            array_shift($revPriceArray);
            array_shift($revVolArray);
        }
    }
?>
    
<script type="text/javascript" lang="Javascript">
    DisplayPriceVolumeChart();
    
    function GetLatestDate()
    {
        var today = new Date("<?php echo $formattedDate; ?>");
        var dd = today.getDate()+1;
        var mm = today.getMonth()+1; //January is 0!
        var yyyy = today.getFullYear();
        if(dd<10) 
        {
            dd = '0'+dd;
        }
        if(mm<10) 
        {
            mm = '0'+mm;
        } 
        today = mm + '/' + dd + '/' + yyyy;
        return today;      
    }
    
    function DisplayPriceVolumeChart()
    {
    Highcharts.chart('chartid', {
    title: 
    {
        text: 'Stock Price (' + GetLatestDate() + ')'
    },

    subtitle: 
    {
        useHTML: true,
        text: '<a target="_blank" href="https://www.alphavantage.co/">Source: Alpha Vantage</a>'
    },
    
    xAxis: 
    {
        categories: [<?php echo join($revDateArray,','); ?>],
        tickInterval: 5
    },
        
    yAxis: 
    [{
        title: 
        {
            text: 'Stock Price'
        },
        allowDecimals: false,
        tickInterval: 5,
        min:<?php echo ($minPrice-20>0)?$minPrice-20 : 0 ; ?>
        
    }, {
        title: 
        {
            text: 'Volume',
        },
        allowDecimals: false,
        labels: 
        {
            format: '{value}M'
        },
        opposite: true,
        max: <?php echo $maxVol*3.5; ?>
    }],
        
    series: 
    [{
        type: 'area',
        name: '<?php echo $symbol; ?>',
        data: [<?php echo join($revPriceArray,','); ?>],
        color: 'rgb(225, 18,17 )',
        tooltip: {
            valueDecimals:2
        }
    }, {
        type: 'column',
        name: '<?php echo $symbol . " Volume"; ?>',
        data: [<?php echo join($revVolArray,','); ?>],
        color: 'white',
        yAxis: 1,
        tooltip: {
            pointFormatter: function() 
            {
                return '<span style="color:' + this.series.color +'">\u25CF </span>' + this.series.name + ' : ' + ((this.y*1000000).toLocaleString()).replace(',',' ').replace(',',' ').replace(',',' ');
            },
            useHTML: true
        }
    }],
        
    legend: 
        {
            enabled: true,
            align: 'right',
            verticalAlign: 'top',
            layout: 'vertical',
            x: 0,
            y: 200
        },
       
    plotOptions: {
            area: {
                fillColor: {
                    linearGradient: {
                        x1: 0,
                        y1: 0,
                        x2: 0,
                        y2: 1
                    },
                    stops: [
                        [0, 'rgba(238,127,127,0.8)'],
                        [1, 'rgba(238,127,127,0.8)']
                    ]
                },
                marker: {
                    radius: 0.5
                },
                lineWidth: 1,
                states: {
                    hover: {
                        lineWidth: 1
                    }
                },
                threshold: null
            },
        column: {
            pointPadding: 0.2,
            groupPadding: 0.2
        }
        }
    });
    }
    
    function DisplayIndicatorChart1(indicator)
    {
        var symbol = '<?php echo $symbol; ?>';
        var url="https://www.alphavantage.co/query?function="+indicator+"&symbol="+symbol+"&interval=daily&time_period=10&series_type=close&apikey=JXIE9QZNR7RYE9KA&nbdevup=3&nbdevdn=3";
    
        if (window.XMLHttpRequest)
        {
                // For IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest(); 
        } 
        else 
        {
                // For IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP"); 
        }
              
        xmlhttp.onreadystatechange = function() 
        {
            if (this.readyState == 4 && this.status == 200) 
            {
                response = this.responseText;
                jsonObj= JSON.parse(response);
                GenerateChart1(jsonObj, symbol, indicator);
            }
        };
        xmlhttp.open("GET", url, true);
        xmlhttp.send();
    }
    
    function GenerateChart1(jsonObj, symbol, indicator)
    {
        var title='';
        
        root=jsonObj.DocumentElement;
        var metadataKey="Meta Data";
        var indicatorKey="2: Indicator";
        if(jsonObj[metadataKey]!=null)
        {
            title=jsonObj[metadataKey][indicatorKey];
        }
        
        var today = new Date();
        var datesArray=[];
        var pricesArray=[];
        
        var revDatesArray=[];
        var revPricesArray=[];
        
        var techKey="Technical Analysis: "+indicator;
        if(jsonObj[techKey]!=null)
        {
            var values=(jsonObj[techKey]);
            var tempDateArray=Object.keys(values);
            var count=tempDateArray.length;
            for(i=0;i<count;i++)
            {
                var dateVal=(tempDateArray[i].split(' '))[0];
                var date = new Date(dateVal);
                var timeDiff = Math.abs(date.getTime() - today.getTime());
                var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
                
                if(diffDays>181)
                {
                    break;
                }
                else
                {
                    var dd = date.getDate()+1;
                    var mm = date.getMonth()+1; //January is 0!

                    if(dd<10) 
                    {
                        dd = '0'+dd;
                    }
                    if(mm<10) 
                    {
                        mm = '0'+mm;
                    } 
                    date = mm + '/' + dd;
                    datesArray[i]=date;
                    price=values[tempDateArray[i]][indicator];
                    pricesArray[i]=parseFloat(price);
                } 
            }
        }
        
        revDatesArray=datesArray.reverse();
        revPricesArray=pricesArray.reverse();
        
        var dateCount=revDatesArray.length;
        if((dateCount-1)%5 !=0)
        {
            var ind=(dateCount-1)%5;
            for(j=0;j<ind;j++)
            {
                revDatesArray.shift();
                revPricesArray.shift();
            }
        }
       
        Highcharts.chart('chartid', {
            
        title: 
        {
            text: title
        },

        subtitle: 
        {
            useHTML: true,
            text: '<a target="_blank" href="https://www.alphavantage.co/">Source: Alpha Vantage</a>'
        },
        
        legend: 
        {
            enabled: true,
            align: 'right',
            verticalAlign: 'top',
            layout: 'vertical',
            x: 0,
            y: 200
        },
        
        xAxis: 
        {
            categories: revDatesArray,
            tickInterval: 5,
        },
        yAxis: 
        {
            title: 
            {
                text: indicator
            },
            allowDecimals: true
        },
            
        series: 
        [{
            name: symbol,
            data: revPricesArray,
            color: 'rgb(208, 92,81 )',
            marker: {
                enabled: true,
                radius: 3
            }
        }],
        
        plotOptions: 
        {
            series: {
                label: {
                    connectorAllowed: false
                }
            }
        } 
        }); 
    }
    
    function DisplayIndicatorChart2(indicator)
    {
        var symbol = '<?php echo $symbol; ?>';
        var url="https://www.alphavantage.co/query?function="+indicator+"&symbol="+symbol+"&interval=daily&time_period=10&series_type=close&apikey=JXIE9QZNR7RYE9KA&slowkmatype=1&slowdmatype=1";
    
        if (window.XMLHttpRequest)
        {
                // For IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest(); 
        } 
        else 
        {
                // For IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP"); 
        }
              
        xmlhttp.onreadystatechange = function() 
        {
            if (this.readyState == 4 && this.status == 200) 
            {
                response = this.responseText;
                jsonObj= JSON.parse(response);
                GenerateChart2(jsonObj, symbol, indicator);
            }
        };
        xmlhttp.open("GET", url, true);
        xmlhttp.send();
    }
    
    function GenerateChart2(jsonObj, symbol, indicator)
    {
        var title='';
        
        root=jsonObj.DocumentElement;
        var metadataKey="Meta Data";
        var indicatorKey="2: Indicator";
        if(jsonObj[metadataKey]!=null)
        {
            title=jsonObj[metadataKey][indicatorKey];
        }
        
        var today = new Date();
        var datesArray=[];
        var pricesArray1=[];
        var pricesArray2=[];
        
        var revDatesArray=[];
        var revPricesArray1=[];
        var revPricesArray2=[];
        
        var techKey="Technical Analysis: "+indicator;
        if(jsonObj[techKey]!=null)
        {
            var values=(jsonObj[techKey]);
            var tempDateArray=Object.keys(values);
            var innerKeys=values[tempDateArray[0]];
            key1=Object.keys(innerKeys)[0];
            key2=Object.keys(innerKeys)[1];
            
            var count=tempDateArray.length;
            for(i=0;i<count;i++)
            {
                var dateVal=(tempDateArray[i].split(' '))[0];
                var date = new Date(dateVal);
                var timeDiff = Math.abs(date.getTime() - today.getTime());
                var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
                
                if(diffDays>181)
                {
                    break;
                }
                else
                {
                    var dd = date.getDate()+1;
                    var mm = date.getMonth()+1; //January is 0!

                    if(dd<10) 
                    {
                        dd = '0'+dd;
                    }
                    if(mm<10) 
                    {
                        mm = '0'+mm;
                    } 
                    date = mm + '/' + dd;
                    datesArray[i]=date;
                   
                    price1=values[tempDateArray[i]][key1];
                    pricesArray1[i]=parseFloat(price1);
                    
                    price2=values[tempDateArray[i]][key2];
                    pricesArray2[i]=parseFloat(price2);
                } 
            }
        }
        
        revDatesArray=datesArray.reverse();
        revPricesArray1=pricesArray1.reverse();
        revPricesArray2=pricesArray2.reverse();
        
        var dateCount=revDatesArray.length;
        if((dateCount-1)%5 !=0)
        {
            var ind=(dateCount-1)%5;
            for(j=0;j<ind;j++)
            {
                revDatesArray.shift();
                revPricesArray1.shift();
                revPricesArray2.shift();
            }
        }
        
        Highcharts.chart('chartid', {
            
        title: 
        {
            text: title
        },

        subtitle: 
        {
            useHTML: true,
            text: '<a target="_blank" href="https://www.alphavantage.co/">Source: Alpha Vantage</a>'
        },
        
        legend: 
        {
            enabled: true,
            align: 'right',
            verticalAlign: 'top',
            layout: 'vertical',
            x: 0,
            y: 200
        },
        
        xAxis: 
        {
            categories: revDatesArray,
            tickInterval: 5
        },
        yAxis: 
        {
            title: 
            {
                text: indicator
            },
            allowDecimals: true
        },
            
        series: 
        [{
            name: symbol+' '+key1,
            data: revPricesArray1,
            marker: {
                enabled: true,
                radius: 3
            }
        },{
            name: symbol+' '+key2,
            data: revPricesArray2,
            marker: {
                enabled: true,
                radius: 4
            }
        }],
        
        plotOptions: 
        {
            series: {
                label: {
                    connectorAllowed: false
                }
            }
        } 
        });
    }
    
    function DisplayIndicatorChart3(indicator)
    {
        var symbol = '<?php echo $symbol; ?>';
        var url="https://www.alphavantage.co/query?function="+indicator+"&symbol="+symbol+"&interval=daily&time_period=10&series_type=close&apikey=JXIE9QZNR7RYE9KA&nbdevup=3&nbdevdn=3";
        
        if (window.XMLHttpRequest)
        {
                // For IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest(); 
        } 
        else 
        {
                // For IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP"); 
        }
              
        xmlhttp.onreadystatechange = function() 
        {
            if (this.readyState == 4 && this.status == 200) 
            {
                response = this.responseText;
                jsonObj= JSON.parse(response);
                GenerateChart3(jsonObj, symbol, indicator);
            }
        };
        xmlhttp.open("GET", url, true);
        xmlhttp.send();
    }
    
    function GenerateChart3(jsonObj, symbol, indicator)
    {
        var title='';
        
        root=jsonObj.DocumentElement;
        var metadataKey="Meta Data";
        var indicatorKey="2: Indicator";
        if(jsonObj[metadataKey]!=null)
        {
            title=jsonObj[metadataKey][indicatorKey];
        }
        
        var today = new Date();
        var datesArray=[];
        var pricesArray1=[];
        var pricesArray2=[];
        var pricesArray3=[];
        
        var revDatesArray=[];
        var revPricesArray1=[];
        var revPricesArray2=[];
        var revPricesArray3=[];
        
        var techKey="Technical Analysis: "+indicator;
        if(jsonObj[techKey]!=null)
        {
            var values=(jsonObj[techKey]);
            var tempDateArray=Object.keys(values);
            var innerKeys=values[tempDateArray[0]];
            key1=Object.keys(innerKeys)[0];
            key2=Object.keys(innerKeys)[1];
            key3=Object.keys(innerKeys)[2];
            
            var count=tempDateArray.length;
            for(i=0;i<count;i++)
            {
                var dateVal=(tempDateArray[i].split(' '))[0];
                var date = new Date(dateVal);
                var timeDiff = Math.abs(date.getTime() - today.getTime());
                var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
                
                if(diffDays>181)
                {
                    break;
                }
                else
                {
                    var dd = date.getDate()+1;
                    var mm = date.getMonth()+1; //January is 0!

                    if(dd<10) 
                    {
                        dd = '0'+dd;
                    }
                    if(mm<10) 
                    {
                        mm = '0'+mm;
                    } 
                    date = mm + '/' + dd;
                    datesArray[i]=date;
                   
                    price1=values[tempDateArray[i]][key1];
                    pricesArray1[i]=parseFloat(price1);
                    
                    price2=values[tempDateArray[i]][key2];
                    pricesArray2[i]=parseFloat(price2);
                    
                    price3=values[tempDateArray[i]][key3];
                    pricesArray3[i]=parseFloat(price3);
                } 
            }
        }
        
        revDatesArray=datesArray.reverse();
        revPricesArray1=pricesArray1.reverse();
        revPricesArray2=pricesArray2.reverse();
        revPricesArray3=pricesArray3.reverse();
        
        var dateCount=revDatesArray.length;
        if((dateCount-1)%5 !=0)
        {
            var ind=(dateCount-1)%5;
            for(j=0;j<ind;j++)
            {
                revDatesArray.shift();
                revPricesArray1.shift();
                revPricesArray2.shift();
                revPricesArray3.shift();
            }
        }
        
        Highcharts.chart('chartid', {
        chart: 
        {
            type: 'line'
        },
        
        title: 
        {
            text: title
        },

        subtitle: 
        {
            useHTML: true,
            text: '<a target="_blank" href="https://www.alphavantage.co/">Source: Alpha Vantage</a>'
        },
        
        legend: 
        {
            enabled: true,
            align: 'right',
            verticalAlign: 'top',
            layout: 'vertical',
            x: 0,
            y: 200
        },
        
        xAxis: 
        {
            categories: revDatesArray,
            tickInterval: 5
        },
        yAxis: 
        {
            title: 
            {
                text: indicator
            },
            allowDecimals: true
        },
            
        series: 
        [{
            name: symbol+' '+key1,
            data: revPricesArray1,
            marker: {
                enabled: true,
                radius: 3
            }
            
        },{
            name: symbol+' '+key2,
            data: revPricesArray2,
            marker: {
                enabled: true,
                radius: 4
            }
        },
        {
            name: symbol+' '+key3,
            data: revPricesArray3,
            marker: {
                enabled: true,
                radius: 3
            }
        }],
        plotOptions: 
        {
            series: {
                label: {
                    connectorAllowed: false
                }
            }
        } 
        });
    } 
    
</script>

</div>
<div class="newsdiv">
    <div class="image">
        <button onClick='ToggleNews()' class="newsbutton">click to show stock news<br>
        <img src="http://cs-server.usc.edu:45678/hw/hw6/images/Gray_Arrow_Down.png" height=30px>
        </button>
    </div>
    <div class="news">

<?php
    $newsUrl="https://seekingalpha.com/api/sa/combined/" .$symbol . ".xml";
    $xml=simplexml_load_file($newsUrl) or die("Error: Cannot create object");
    $jsonNews = json_encode($xml);
    $jsonObj=json_decode($jsonNews);
    
    echo "<table class=\"newstable\" style=display:\"none\">";
    
    $channelKey="channel";
    $itemKey="item";
    $linkKey="link";
    $titleKey="title";
    $pubKey="pubDate";
    $items=$jsonObj->$channelKey->$itemKey;
    $count=1;
    for($i=0; $i<(sizeof($items)); $i++)
    {
        if($count==6)
        {
            break;
        }
        
        $itemLink=$items[$i]->$linkKey;
        if(strpos($itemLink, "/article/") !==false)
        {
            $count=$count+1;
            $itemTitle=$items[$i]->$titleKey;
            $itemPub=$items[$i]->$pubKey;
            $pos=strpos($itemPub, '-');
            if ($pos !== false) 
            {
                $itemPub = substr($itemPub, 0, $pos-1);
            }
            echo "<tr><td class=\"newsrow\">";
            echo "<a target=\"_blank\" href=" . "\"" . $itemLink . "\"" . ">" . $itemTitle . "</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Publicated Time: " . $itemPub;
            echo "</td></tr>";
        }
        else
        {
            continue;
        }
    }
    echo "</table>";   
?>
    </div>   
</div>
    
<?php
}
?>
</html>