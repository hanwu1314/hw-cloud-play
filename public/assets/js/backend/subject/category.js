define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'subject/category/index' + location.search,
                    add_url: 'subject/category/add',
                    edit_url: 'subject/category/edit',
                    del_url: 'subject/category/del',
                    table: 'subject_category',
                }
            });
            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id', // 主键
                sortName: 'weight', // 排序
                columns: [
                    [
                        { checkbox: true },// 复选框
                        // __('Id') 根据当前语言环境读取多语言Id对应的值  sortable是否开启排序按钮
                        { field: 'id', title: __('Id'), sortable: true },
                        { field: 'name', title: __('Name') },
                        { field: 'weight', title: __('Weight') },
                        { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    }
    return Controller;
});
