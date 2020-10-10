<template>
    <div class="mt-2">
        <b-row align-h="between" align-v="center">
            <b-col style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                <b-icon v-if="this.status == 'pending'" icon="arrow-repeat" animation="spin" font-scale="1"></b-icon>
                <b-icon v-if="this.status == 'success'" icon="check2" class="text-success" font-scale="1"></b-icon>
                <b-icon v-if="this.status == 'error'" icon="x" class="text-danger" font-scale="1"></b-icon>
                <i class="ml-1">{{ this.title }}</i>
                <b-progress class="mb-2" :value="this.current" :max="this.tasks.length" :animated="!this.finished" show-progress></b-progress>
            </b-col>
            <b-col cols="auto" v-if="!this.finished">
                  <b-button block v-if="this.resume" @click="resume = !resume" variant="danger">Durdur</b-button>
                  <b-button block v-else variant="success" @click="resume = !resume; next();">Devam et</b-button>
            </b-col>
            <b-col cols="12">
                <a href="#" @click="hideOutput = !hideOutput">
                    <b-icon v-if="this.hideOutput" icon="caret-down-fill" class="mr-1"></b-icon>
                    <b-icon v-else icon="caret-up-fill" class="mr-1"></b-icon>
                    <span v-if="this.hideOutput">Göster</span>
                    <span v-else>Gizle</span>
                </a>
                <div v-if="!this.hideOutput" style="width: %100; height: 300px; overflow: auto; background: black;" id="outputArea" class="mt-2">
                    <pre style="color: lime; margin-bottom: 0;"><code>{{ this.output }}</code></pre>
                </div>
            </b-col>
        </b-row>
    </div>
</template>
<script>
import { LayoutPlugin, BIcon, ProgressPlugin, BButton } from 'bootstrap-vue'

export default {
    name: "task",
    components: {
        LayoutPlugin,
        BIcon,
        ProgressPlugin,
        BButton
    },
    props: {
        tasks: {
            type: Array,
            required: true
        },
        onSuccess: {
            type: String
        },
        onFail: {
            type: String
        }
    },
    methods: {
        process: function() {
            let task = this.tasks[this.current];
            let formData = new FormData();
            formData.append("name", task.name);
            formData.append("attributes", JSON.stringify(task.attributes));
            this.status = "pending";
            let vm = this;
            request(this.API("runTask"), formData, function(res) {
                let response = JSON.parse(res);
                vm.title = response.message.description
                vm.appendOutput(response.message.description, true);
                vm.appendOutput(response.message.output);
                if(response.message.status == "started"){
                    vm.check();
                }else if(response.message.status == "failed"){
                    vm.status = "error";
                    vm.hideOutput = false;
                    if(window[vm.onFail]){
                        window[vm.onFail]();
                    }
                }else if(response.message.status == "conflict"){
                    vm.status = "pending";
                    vm.hideOutput = false;
                    vm.appendOutput("Benzer bir işlem zaten devam ediyor. Tekrar deneniyor...", true);
                    setTimeout(function(){
                        vm.process();
                    }, 1000);
                }
            }, function(response){
                let error = JSON.parse(response);
                showSwal(error.message,'error',2000);
            });
        },
        next: function() {
            if(!this.resume)
                return;
            if(this.tasks[this.current + 1]){
                this.current += 1;
                this.process();
            }else{
                this.current += 1;
                this.finished = true;
                this.status = "success";
                if(window[this.onSuccess]){
                    window[this.onSuccess]();
                }
            }
        },
        check: function() {
            let task = this.tasks[this.current];
            let formData = new FormData();
            formData.append("name", task.name);
            formData.append("attributes", JSON.stringify(task.attributes));
            let vm = this;
            request(this.API("checkTask"), formData, function(res) {
                let response = JSON.parse(res);
                vm.appendOutput(response.message.output);
                if(response.message.status == "pending"){
                    setTimeout(function(){
                        vm.check();
                    }, 1000);
                }else if (response.message.status == "success"){
                    vm.next();
                }else if(response.message.status == "failed"){
                    vm.status = "error";
                    vm.hideOutput = false;
                    if(window[vm.onFail]){
                        window[vm.onFail]();
                    }
                }
            }, function(response){
                let error = JSON.parse(response);
                showSwal(error.message,'error',2000);
            });
        },
        appendOutput: function (output, system=false) {
            if(output){
                if(system){
                    output = ">>> " + output;
                }
                if(this.output){
                    output = "\n" + output;
                    if(system){
                        output = "\n" + output;
                    }
                }
                this.output += output;
            }
        },
        scrollToEnd: function () {
            var container = this.$el.querySelector("#outputArea");
            if(container){
                container.scrollTop = container.scrollHeight;
            }
        }
    },
    data(){
        return {
            current: 0,
            output: "",
            title: "",
            status: "pending",
            finished: false,
            resume: true,
            hideOutput: false
        };
    },
    mounted() {
        this.process();
    },
    updated() {
        this.$nextTick(() => this.scrollToEnd());
    }
}
</script>
