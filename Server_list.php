<!DOCTYPE html>
<html>
<head>
	<title>WDP Server Info</title>
    <meta charset="UTF-8">
    <style>
        table {
            border: 2px solid black;
            /* border-collapse: collapse; */
            padding: 5px;
            white-space: nowrap; /* 防止自動換行 */
            margin-top: 10px; /* 與上方分隔的距離 */
        }

        td {
            border: 2px solid black;
            padding: 5px;
        }
        
        hr {
            border: 2px solid black;
        }
        
        
        /* 單雙分色,增加可讀性 
        tr:nth-child(even) {background: #CCC}
        tr:nth-child(odd) {background: #FFF} 
        */

        button { /* 一般按鈕 */
            background-color: #FF9224; 
            color: #000;
            border: outset #ADADAD;
            padding: 10px 20px; 
            margin: 0px 10px; /* 與旁邊元素分隔的距離 , 上下/左右 */
            margin-bottom: 10px; /* 與下方分隔的距離 */

            text-align: center;
            font-size: 20px;
            font-weight: bold;
            /*
            字體的陰影 , 太醜所以拿掉
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            text-decoration: none;
            */

            display: inline-block;
            border-radius: 8px; /* 邊角原切 */
            box-shadow: 0px 8px 10px rgba(0, 0, 0, 0.8); /* 外框陰影 */
            cursor: pointer; /* 滑鼠放在上方時的圖案 */
        }

        button:hover { /* 一般按鈕 */
            /* 滑鼠放在上方時的設定 */
            background-color: #0066CC;
            color: #FFF;
            border: outset #ADADAD;
        }

        /* 排序按鈕 */
        .asc-btn, .desc-btn {
            background-color: transparent;
            color: #FFF;
            font-size: 12px;
            border: none;
            box-shadow: 0px 0px 6px rgba(0, 0, 0, 0.3); /* 外框陰影 */
            cursor: pointer;
            display: inline-block;
            position: relative;
        }
        
        .asc-btn:hover, .desc-btn:hover {
            border: none;
            position: relative;
        }

        label {
            font-size: 20px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-left: 10px;
        }

        input {
            font-size: 20px;
            /* font-weight: bold; */
            background-color: #E0E0E0;
            color: black; 
            border: dotted #D0D0D0;
        }
        
        /* 表頭固定 */
        .table-container {
            position: relative;
        }

        /* 表頭設定 */
        #server_info_table thead.sticky th { /* 表頭固定 */
            border: 2px solid black;
            padding: 5px;
            background: #0072E3;
            font-size: 20px;
            color: 	#FFF;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        /* 隱藏表頭/內容 */
        .hidden {
            display: none;
        }

        /* 下拉選單 */
        select {
            font-size: 16px;
            font-weight: 550; 
            padding: 5px;
            border: double  #D0D0D0;
            border-radius: 8px;
            background-color: #E0E0E0;
            color: black;
            /* box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.8); */
            cursor: pointer;
        }

        select:hover {
            background-color: #FFD306;
            color: black;
            border: double  #EAC100;
        }

        select option {
            background-color: #FFE66F;
            color: black;
        }

    </style>
    <script src='http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js'></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // 排序按鈕
        $(document).ready(function() {
            $('th').each(function(col) {
                $(this).append('<div class="sort-arrows"><button class="asc-btn">&#9650;</button><button class="desc-btn">&#9660;</button></div>');
                $(this).find('.asc-btn').click(function() {
                    sortTable(col, 'asc');
                });
                $(this).find('.desc-btn').click(function() {
                    sortTable(col, 'desc');
                });
            });

            function sortTable(col, order) {
                var rows = $('table').find('tbody > tr').get();
                rows.sort(function(a, b) {
                    var A = $(a).children('td').eq(col).text().toUpperCase();
                    var B = $(b).children('td').eq(col).text().toUpperCase();

                    // 判斷是否為數字或IP，若是則轉換成數字或IP後比較
                    if (!isNaN(A) && !isNaN(B)) {
                        A = Number(A);
                        B = Number(B);
                    } else if (isValidIP(A) && isValidIP(B)) {
                        A = parseInt(A.split('.')[3]);
                        B = parseInt(B.split('.')[3]);
                    }

                    // 比較大小
                    if (order == 'asc') {
                        return (A < B ? -1 : A > B ? 1 : 0);
                    } else {
                        return (A > B ? -1 : A < B ? 1 : 0);
                    }
                });
                $.each(rows, function(index, row) {
                    $('tbody').append(row);
                });
                $('th').removeClass('asc desc');
                $('th').eq(col).addClass(order);
            }

            // 判斷是否為合法IP
            function isValidIP(str) {
                var regex = /^(\d{1,3}\.){3}\d{1,3}$/;
                return regex.test(str);
            }
        });
    </script>
</head>
<body>
    <img src="標題圖片">
    <hr>

	<form method="post" action="">
		<button type="submit" name="server_button">Server</button>
        <button type="submit" name="disk_button">DISK</button>
		<button type="submit" name="nic_button">NIC</button>
        <button type="submit" name="ilo_dl380_button">iLO_DL380</button>
        <button type="submit" name="ilo_dx380_button">iLO_DX380</button>
	</form>
    <hr>

    <!-- 隱藏/顯示欄位的下拉選單 -->
    <label for="filter">Show columns:</label>
    <select id="table_select"></select>

    <label for="search_function">Search： </label> 
    <input type="text" id="search_function" name="search_function" onkeyup="searchTable()" placeholder="Looking for...">
    
	<?php
		if(isset($_POST['server_button'])) {
            echo '<div class="table-container">';
			echo '<table id="server_info_table">';
			echo '<thead class="sticky">';
			echo '<tr>';
            echo '<th>FUNCTION</th>';
            echo '<th>NTNX_CLUSTER</th>';
            echo '<th>VMWARE_CLUSTER</th>';
            echo '<th>HOSTNAME</th>';
			echo '<th>IP</th>';
            echo '<th>CVM</th>';
			echo '<th>IPMI</th>';
			echo '<th>SN</th>';
			echo '<th>VENDOR</th>';
			echo '<th>MODEL</th>';
            echo '<th>MODE</th>';
            echo '<th>SNMP</th>';
            echo '<th>UPTIME</th>';
            echo '<th>CORE_CAP</th>';
            echo '<th>CORE_USED</th>';
            echo '<th>MEM_CAP</th>';
            echo '<th>MEM_USED</th>';
            echo '<th>NICS</th>';
            echo '<th>VLAN</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

			ini_set('display_errors', 1);
			error_reporting(E_ALL);
			require_once 'DB登入檔';

			// 定義各個 table 名稱
			// $table_name = 'table名稱';

			// 查詢資料
			$sql = "select語法";

			$result = $conn->query($sql);

			while ($row = $result->fetch_assoc()) {
				echo '<tr>';
                echo '<td>' . $row['FUNCTION'] . '</td>';
                echo '<td>' . $row['NTNX_CLUSTER'] . '</td>';
                echo '<td>' . $row['VMWARE_CLUSTER'] . '</td>';
                echo '<td>' . $row['HOSTNAME'] . '</td>';
				echo '<td>' . $row['IP'] . '</td>';
                echo '<td>' . $row['CVM'] . '</td>';
				echo '<td>' . $row['IPMI'] . '</td>';
				echo '<td>' . $row['SN'] . '</td>';
				echo '<td>' . $row['VENDOR'] . '</td>';
				echo '<td>' . $row['MODEL'] . '</td>';
                echo '<td>' . $row['MODE'] . '</td>';
                echo '<td>' . $row['SNMP'] . '</td>';
                echo '<td>' . $row['UPTIME'] . '</td>';
                echo '<td>' . $row['CORE_CAP'] . '</td>';
                echo '<td>' . $row['CORE_USED'] . '</td>';
                echo '<td>' . $row['MEM_CAP'] . '</td>';
                echo '<td>' . $row['MEM_USED'] . '</td>';
                echo '<td>' . $row['NICS'] . '</td>';
                echo '<td>' . $row['VLAN'] . '</td>';
				echo '</tr>';
			}

			// 關閉連接
			$conn->close();

			echo '</tbody>';
			echo '</table>';
			echo '</div>';
		}
		if(isset($_POST['nic_button'])) {
            echo '<div class="table-container">';
			echo '<table id="server_info_table">';
			echo '<thead class="sticky">';
			echo '<tr>';
			echo '<th>IP</th>';
			echo '<th>NIC_NAME</th>';
			echo '<th>NIC_SPEED</th>';
			echo '<th>CON_SWITCH</th>';
			echo '<th>CON_PORT</th>';
			echo '<th>SWITCH_VLAN</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

			ini_set('display_errors', 1);
			error_reporting(E_ALL);
			require_once 'DB登入檔';

			// 定義各個 table 名稱
			$table_name = 'table名稱';

			// 查詢資料
			$sql = "select語法";
			$result = $conn->query($sql);

			while ($row = $result->fetch_assoc()) {
				echo '<tr>';
				echo '<td>' . $row['IP'] . '</td>';
				echo '<td>' . $row['NIC_NAME'] . '</td>';
				echo '<td>' . $row['NIC_SPEED'] . '</td>';
				echo '<td>' . $row['CON_SWITCH'] . '</td>';
				echo '<td>' . $row['CON_PORT'] . '</td>';
				echo '<td>' . $row['SWITCH_VLAN'] . '</td>';
				echo '</tr>';
			}

			// 關閉連接
			$conn->close();

			echo '</tbody>';
			echo '</table>';
			echo '</div>';
		}
        if(isset($_POST['disk_button'])) {
            echo '<div class="table-container">';
			echo '<table id="server_info_table">';
			echo '<thead class="sticky">';
			echo '<tr>';
			echo '<th>TYPE</th>';
			echo '<th>MODEL</th>';
			echo '<th>SIZE</th>';
			echo '<th>SN</th>';
			echo '<th>UUID</th>';
			echo '<th>FIRMWARE</th>';
            echo '<th>HOST</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

			ini_set('display_errors', 1);
			error_reporting(E_ALL);
			require_once 'DB登入檔';

			// 定義各個 table 名稱
			$table_name = 'table名稱';

			// 查詢資料
			$sql = "select語法";
			$result = $conn->query($sql);

			while ($row = $result->fetch_assoc()) {
				echo '<tr>';
				echo '<td>' . $row['DISK_TYPE'] . '</td>';
				echo '<td>' . $row['MODEL'] . '</td>';
				echo '<td>' . $row['SIZE'] . '</td>';
				echo '<td>' . $row['SN'] . '</td>';
				echo '<td>' . $row['DISK_UUID'] . '</td>';
				echo '<td>' . $row['FIRMWARE'] . '</td>';
                echo '<td>' . $row['IP'] . '</td>';
				echo '</tr>';
			}

			// 關閉連接
			$conn->close();

			echo '</tbody>';
			echo '</table>';
			echo '</div>';
		}
        if(isset($_POST['ilo_dl380_button'])) {
            echo '<div class="table-container">';
			echo '<table id="server_info_table">';
			echo '<thead class="sticky">';
			echo '<tr>';
			echo '<th>IP</th>';
            echo '<th>iLO_5</th>';
			echo '<th>System_ROM</th>';
			echo '<th>Redundant_System_ROM</th>';
			echo '<th>NVIDIA_A40</th>';
            echo '<th>HPE_SR932i-p_Gen10+</th>';
            echo '<th>Innovation_Engine_Firmware</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

			ini_set('display_errors', 1);
			error_reporting(E_ALL);
			require_once 'DB登入檔';

			// 定義各個 table 名稱
			$table_name = 'table名稱';

			// 查詢資料
			$sql = "select語法";
			$result = $conn->query($sql);

			while ($row = $result->fetch_assoc()) {
				echo '<tr>';
				echo '<td>' . $row['IP'] . '</td>';
                echo '<td>' . $row['iLO_5'] . '</td>';
                echo '<td>' . $row['System_ROM'] . '</td>';
				echo '<td>' . $row['Redundant_System_ROM'] . '</td>';
				echo '<td>' . $row['NVIDIA_A40'] . '</td>';
				echo '<td>' . $row['HPE_SR932i-p_Gen10+'] . '</td>';
				echo '<td>' . $row['Innovation_Engine_Firmware'] . '</td>';
				echo '</tr>';
			}

			// 關閉連接
			$conn->close();

			echo '</tbody>';
			echo '</table>';
			echo '</div>';
		}
        if(isset($_POST['ilo_dx380_button'])) {
            echo '<div class="table-container">';
			echo '<table id="server_info_table">';
			echo '<thead class="sticky">';
			echo '<tr>';
			echo '<th>IP</th>';
            echo '<th>iLO_5</th>';
			echo '<th>System_ROM</th>';
			echo '<th>Redundant_System_ROM</th>';
			echo '<th>HPE_Smart_Array_E208i-p_SR_Gen10</th>';
            echo '<th>HPE_Smart_Array_E208i-a_SR_Gen10</th>';
            echo '<th>HPE_NS204i-p_Gen10+_Boot_Controller</th>';
            echo '<th>Innovation_Engine_Firmware</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

			ini_set('display_errors', 1);
			error_reporting(E_ALL);
			require_once 'DB登入檔';

			// 定義各個 table 名稱
			$table_name = 'table名稱';

			// 查詢資料
			$sql = "select語法";
			$result = $conn->query($sql);

			while ($row = $result->fetch_assoc()) {
				echo '<tr>';
				echo '<td>' . $row['IP'] . '</td>';
                echo '<td>' . $row['iLO_5'] . '</td>';
                echo '<td>' . $row['System_ROM'] . '</td>';
				echo '<td>' . $row['Redundant_System_ROM'] . '</td>';
				echo '<td>' . $row['HPE_Smart_Array_E208i-p_SR_Gen10'] . '</td>';
				echo '<td>' . $row['HPE_Smart_Array_E208i-a_SR_Gen10'] . '</td>';
				echo '<td>' . $row['HPE_NS204i-p_Gen10+_Boot_Controller'] . '</td>';
                echo '<td>' . $row['Innovation_Engine_Firmware'] . '</td>';
				echo '</tr>';
			}

			// 關閉連接
			$conn->close();

			echo '</tbody>';
			echo '</table>';
			echo '</div>';
		}
	?>

    <script>
        /* 搜尋功能 */
        function searchTable() {
            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("search_function");
            filter = input.value.toLowerCase();
            table = document.querySelector("#server_info_table tbody"); /* 搜尋時只影響內容 , 不影響表頭 */
            tr = table.getElementsByTagName("tr");

            for (i = 0; i < tr.length; i++) {
                    var found = false;
                    for (j = 0; j < tr[i].cells.length; j++) {
                    td = tr[i].cells[j];
                    if (td) {
                        txtValue = td.textContent || td.innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                        }
                    }
                }
                if (found) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }

        /* back-to-top按鈕 */
        $(document).ready(function () {
            // 捲軸偵測距離頂部超過 100 才顯示按鈕
            $(window).scroll(function () {
                if ($(window).scrollTop() > 100) {
                if ($(".back-top").hasClass("hide")) {
                    $(".back-top").toggleClass("hide");
                }
                } else {
                $(".back-top").addClass("hide");
                }
            });

            // 點擊按鈕回頂部
            $(".back-top").on("click", function (event) {
                $("html, body").animate(
                {
                    scrollTop: 0
                },
                500 // 回頂部時間為 500 毫秒
                );
            });
        });
        //<![CDATA[
            (function () {
                var imgWidth = 50, // 圖片寬度
                imgHeight = 50, // 圖片高度
                imgSrc = "back-to-top圖片", // 圖片本人
                locatioin = 14/15, // 按鈕出現在螢幕的高度,放置在右下方
                right = 10, // 距離右邊px值
                opacity = 0.3, // 透明度
                speed = 500, // 捲動速度
                $button = $("<img id='goTopButton' style='display: none; z-index: 5; cursor: pointer;' title='回到頂端'/>").appendTo("body"),
                $body = $(document),
                $win = $(window);
                $button.css({ // 配合變數更改圖片大小(原圖太大)
                    "width": imgWidth + "px",
                    "height": imgHeight + "px",
                    "opacity": opacity,
                    "position": "fixed",
                    "right": right + "px",
                    "top": $win.height() * locatioin + "px",
                }).attr("src", imgSrc).on({
                    mouseover: function() {$button.css("opacity", 1);},
                    mouseout: function() {$button.css("opacity", opacity);},
                    click: function() {$("html, body").animate({scrollTop: 0}, speed);}
                });
                window.goTopMove = function () {
                    var scrollH = $body.scrollTop(),
                    winH = $win.height(),
                    css = {"top": winH * locatioin + "px", "position": "fixed", "right": right + "px", "opacity": opacity};
                    if(scrollH > 100) { 
                        $button.css(css);
                        $button.fadeIn("slow");
                    } else {
                        $button.fadeOut("slow");
                    }
                };
                $win.on({
                    scroll: function() {goTopMove();},
                    resize: function() {goTopMove();}
                });
            })();
        //]]>

        /* 顯示/隱藏欄位 */
        function filterTable() {
            var filter = document.getElementById("filter").value;
            var table = document.getElementById("server_info_table");
            var rows = table.getElementsByTagName("tr");

            for (var i = 0; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");
                if (cells.length > filter) {
                    cells[filter].classList.toggle("hidden");
                }
            }
        }
        const select = document.getElementById('table_select');
        const tables = document.getElementsByTagName('table');
        // 遍歷所有表格
        for (let i = 0; i < tables.length; i++) {
            const table = tables[i];
            const ths = table.querySelectorAll('th'); // 取得表格的所有表頭
            // 把各個表的表頭加到下拉選單中
            for (let j = 0; j < ths.length; j++) {
                const option = document.createElement('option');
                option.value = i + '-' + j; // 用 i 和 j 组合成 value，以便找到對應的表頭
                option.text = ths[j].textContent; // 將表頭文字作為選項文字
                select.add(option);
            }
        }
        // 監聽select元素的change事件
        select.addEventListener('change', function() {
            const value = this.value;
            const [tableIndex, thIndex] = value.split('-'); // 從value中分離出i和j
            const table = tables[tableIndex];
            const th = table.querySelectorAll('th')[thIndex];

            // 找到表頭所在的列，並根據當前選中狀態顯示或隱藏該列
            const columnIndex = Array.from(th.parentNode.children).indexOf(th);
            const rows = table.querySelectorAll('tr');
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cell = row.children[columnIndex];
                if (cell) {
                    const isHidden = cell.classList.contains('hidden');
                    cell.classList.toggle('hidden', !isHidden); // 根據當前選中狀態顯示或隱藏該列
                    const form = cell.querySelector('form'); // 找到表單
                    if (form) {
                        form.classList.toggle('hidden', !isHidden); // 根據當前選中狀態顯示或隱藏該表單
                    }
                }
            }
        });

    </script>
</body>
</html>
