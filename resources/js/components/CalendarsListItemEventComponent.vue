<template>
    <div class="card calendar-single">
<!--        {{ event }}-->
        <div class="row">
            <div class="col-2">
                <div class="data">
                    {{ event.started_at|formatDate }}
                </div>
            </div>
            <div class="col-2">
                <div class="data">
                    {{ event.started_at|formatTime }} - {{ event.ended_at|formatTime }}
                </div>
            </div>

            <div class="col-2">
                <div class="data">
                    {{ event.location }}
                </div>
            </div>

            <div class="col-2">
                <div class="data">
                    {{ event.name }}
                </div>
            </div>

            <div class="col-2">
                <div class="data">
                    {{ event.description }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props:['event'],


        filters: {
            formatDate: function(value) {
                let date = new Date(value);
                let month = date.getMonth() + 1;
                let day = date.getDate();
                let year = date.getFullYear()
                return month+'/'+day+'/'+year;
            },

            formatTime: function(value) {
                let date = new Date(value);
                let hours = date.getHours();
                let minutes = date.getMinutes();
                let ampm = hours >= 12 ? 'pm' : 'am';
                hours = hours % 12;
                hours = hours ? hours : 12;
                minutes = minutes < 10 ? '0'+minutes : minutes;
                return hours+':'+minutes+' '+ampm;
            }
        }
    }
</script>
