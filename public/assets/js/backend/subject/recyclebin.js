define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    var Controller = {
        index: function () {
            // 绑定事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var panel = $($(this).attr("href"));

                if (panel.length > 0) {
                    // 点击选项卡指向到相应的table
                    Controller.table[panel.attr("id")].call(this);
                    $(this).on('click', function (e) {
                        // 模拟点击刷新按钮
                        $($(this).attr("href")).find(".btn-refresh").trigger("click");
                    });
                }

                // 移除绑定的事件
                $(this).unbind('shown.bs.tab');
            });

            //必须默认触发shown.bs.tab事件
            $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");
        },
        table: {
            subject: function () {
                // 初始化表格参数配置
                Table.api.init({
                    extend: {
                        recyclebin_url: 'subject/subject/recyclebin',
                        del_url: 'subject/subject/destroy',
                        restore_url: 'subject/subject/restore',
                        multi_url: 'subject/subject/multi',  // 表格的复选框
                        table: 'subject',  // 课程表
                    }
                });

                var table = $("#table1");

                // 初始化表格
                table.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.recyclebin_url,
                    pk: 'id',
                    sortName: 'createtime',
                    // 工具栏
                    toolbar: '#toolbar1',
                    columns: [
                        [
                            { checkbox: true },
                            { field: 'id', title: __('Id'), sortable: true },
                            { field: 'thumbs_cdn', title: __('Thumbs'), events: Table.api.events.image, formatter: Table.api.formatter.image },
                            { field: 'title', title: __('Title'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content },
                            { field: 'price', title: __('Price'), operate: 'BETWEEN' },
                            { field: 'category.name', title: __('Cateid') },
                            { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                            {
                                field: 'deletetime',
                                title: __('Deletetime'),
                                operate: 'RANGE',
                                addclass: 'datetimerange',
                                formatter: Table.api.formatter.datetime
                            },
                            {
                                field: 'operate',
                                width: '140px',
                                title: __('Operate'),
                                table: table,
                                events: Table.api.events.operate,
                                buttons: [
                                    {
                                        name: 'Restore',
                                        title: __('Restore'),
                                        classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                        icon: 'fa fa-rotate-left',
                                        url: $.fn.bootstrapTable.defaults.extend.restore_url,
                                        refresh: true
                                    }
                                ],
                                formatter: Table.api.formatter.operate
                            }
                        ]
                    ]
                });



                // 给还原按钮绑定一个事件
                $(document).on('click', '.btn-reduction', function () {
                    let ids = Table.api.selectedids(table);

                    layer.confirm('确认还原数据？', { title: '还原' }, function (index) {
                        // 当点击确认按钮自动关闭弹窗
                        layer.close(index);

                        // 发起请求
                        Backend.api.ajax({
                            url: $.fn.bootstrapTable.defaults.extend.restore_url,
                            data: {
                                ids
                            }
                        }, function () {
                            table.bootstrapTable('refresh');
                        })
                    });
                });

                // 为表格绑定事件
                Table.api.bindevent(table);
            },
            order: function () {
                console.log('课程订单回收站');
            }
        }
    }

    return Controller;
});