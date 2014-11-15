/**
 * Created by christopherdergrossewolff on 23.10.14.
 * ergaenzt von Micha Schubert 29.10.14
 */

var i=0, n=0, sum=0;
var zensuren_hilf=zensuren;

function compare(a,b) {
  if (a.date < b.date)
     return -1;
  if (a.date > b.date)
    return 1;
  return 0;
}

zensuren.sort(compare);
for (var i=0; i<zensuren.length; i++) {
	sum+=parseInt(zensuren[i].wert);
	n++;
	zensuren[i].value=sum/n;
}


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
            lineColors: ["gray"],
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
            lineColors: ["gray"],
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
