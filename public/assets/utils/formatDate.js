function formatDate(timestamp, fmt="yyyy/MM/dd hh:mm:ss") {
    const date = new Date(timestamp * 1000); // 将时间戳乘以1000转换为毫秒，并创建 Date 对象
  
    const options = {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    };
  
    fmt = fmt.replace('yyyy', date.toLocaleString('en', { year: 'numeric' })) // 替换年份
      .replace('MM', date.toLocaleString('en', { month: '2-digit' })) // 替换月份
      .replace('dd', date.toLocaleString('en', { day: '2-digit' })) // 替换日期
      .replace('hh', date.toLocaleString('en', { hour: '2-digit', hour12: false })) // 替换小时
      .replace('mm', date.toLocaleString('en', { minute: '2-digit' })) // 替换分钟
      .replace('ss', date.toLocaleString('en', { second: '2-digit' })); // 替换秒数
  
    return fmt;
  }
  
  