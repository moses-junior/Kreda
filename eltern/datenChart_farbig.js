/**
 * Created by christopherdergrossewolff on 23.10.14.
 */

if (notenpunkte)
{
    window.onload = function () {
        new Morris.Line({
            // ID of the element in which to draw the chart.
            element: 'lineChart',
            // Chart data records -- each entry in this array corresponds to a point on
            // the chart.
            data: zensuren,
            // The name of the data record attribute that contains x-values.
            xkey: 'date',
            // A list of names of data record attributes that contain y-values.
            ykeys: ['value'],
            // Labels for the ykeys -- will be displayed when you hover over the
            // chart.
            labels: ['Durchschnitt'],
            xLabels: "month",
            ymin: 3,
            ymax: 15,
            pointSize: "0px",
            lineColors: ["white"],
            xLabelFormat: function (x) {
                var IndexToMonth = [ "Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez" ];
                var month = IndexToMonth[ x.getMonth() ];
                var year = x.getFullYear();
                return month + ' ' + year;
            },
            dateFormat: function (x) {
                var IndexToMonth = [ "Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez" ];
                var month = IndexToMonth[ new Date(x).getMonth() ];
                var year = new Date(x).getFullYear();
                var day = new Date(x).getDate();
                return day + ' ' + month + ' ' + year;
            }
        });

    };
}
else
{
    window.onload = function () {
        new Morris.Line({
            // ID of the element in which to draw the chart.
            element: 'lineChart',
            // Chart data records -- each entry in this array corresponds to a point on
            // the chart.
            data: zensuren,
            // The name of the data record attribute that contains x-values.
            xkey: 'date',
            // A list of names of data record attributes that contain y-values.
            ykeys: ['value'],
            // Labels for the ykeys -- will be displayed when you hover over the
            // chart.
            labels: ['Durchschnitt'],
            xLabels: "month",
            ymin: 5,
            ymax: 1,
            pointSize: "0px",
            lineColors: ["white"],
            xLabelFormat: function (x) {
                var IndexToMonth = [ "Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez" ];
                var month = IndexToMonth[ x.getMonth() ];
                var year = x.getFullYear();
                return month + ' ' + year;
            },
            dateFormat: function (x) {
                var IndexToMonth = [ "Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez" ];
                var month = IndexToMonth[ new Date(x).getMonth() ];
                var year = new Date(x).getFullYear();
                var day = new Date(x).getDate();
                return day + ' ' + month + ' ' + year;
            },
            gridTextColor: "black",
        });

    };
}
