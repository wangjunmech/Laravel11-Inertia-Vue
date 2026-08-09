// 限制输入特殊字符的方法
export function keyUp(data, exclude) {
    //*********** 以#号替代所有正则类的字符限制，如果exclude参数中含有#则其它正则表达示内的符号都允许输入*/
    //     if (exclude === '#') {
    //     e.target.value = e.target.value.replace(/[`~!@$%^&*()_\-+=<>?:"{}|,.\/;'\\[\]·~！@￥%……&*（）——\-+={}|《》？：“”【】、；‘’，。、]/g, '')
    //     } else {
    //     e.target.value = e.target.value.replace(/[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;'\\[\]·~！@#￥%……&*（）——\-+={}|《》？：“”【】、；‘’，。、]/g, '')
    //     }
    //     return e
    // //************ */
    let status = true // 返回状态
    let msg = '' // 返回错误信息
    if (!data) return [true, msg] // 如果字段为空，则跳过，返回true
    const jsFilter = [',', '{', '}', '！', '￥', '(', ')', '【', '】', '·', '“', '’', '。', '，', '', '', '？', '#', '_', '-', '…', '^', '—',
        '+', '|', '%', '<', '>', '&', '\\', '\'', '/', '\\', '\r', '\n', '\\\\', '\t', '\f', '\b', '!', '@', '$', '《', '》', '=',
        '^', '*', '（', '）', '~', '?', ':', ';', '`', '<!--', '--', '->', '/\\*', '/\\\\*', '\\*/', '\\\\*/', 'onfocus',
        'onblur', 'alert', 'location', 'document', 'window', 'onclick', 'javascript', 'script', 'function', 'jscript', 'vbscript',
        'confirm', 'prompt', '()', '//', 'onerror', '/*', '\\u003e', 'eval', 'url', 'expr', 'urlunencoded', 'referrer', 'write', 'writeln',
        'body.innerhtml', 'execscript', 'setinterval', 'settimeout', 'open', 'navigate', 'srcdoc', '%0a', '</', '.', 'URLUnencoded', 'setTimeout']
    if (exclude) {
        if (exclude.length === 1 && jsFilter.indexOf(exclude[0]) !== -1) {
            jsFilter.splice(jsFilter.indexOf(exclude[0]), 1)
        } else if (exclude.length > 1) {
            exclude.forEach(item => {
                if (jsFilter.indexOf(item) !== -1) {
                    jsFilter.splice(jsFilter.indexOf(item), 1)
                }
            })
        }
    }
    const htmlFilter = ['iframe', 'body', 'form', 'base', 'img', 'src', 'style', 'div', 'object', 'meta',
        'link', 'input', 'comment', 'br', '&n']
    const sqlFilter = ['and', 'or', 'exec', 'execute', 'insert', 'select', 'delete', 'update', 'alter', 'create',
        'drop', 'count', '*', 'chr', 'char', 'asc', 'mid', 'substring', 'master', 'truncate', 'declare', 'xp_cmdshell', 'restore',
        'backup', 'like', 'table', 'from', 'grant', 'use', 'column_name', 'group_concat', 'information_schema.columns', 'table_schema', 'union',
        'where', 'order by', 'join', 'modify', 'into', 'substr', 'ascii', 'having']
    const urlFilter = ['%2b', '%20', '%2f', '%3f', '%25', '%23', '%26', '%30', '%27', '%22', '%28', '%29', '%2a', '%2b', '%2d']
    let disInputList = ['onload', 'onclick', 'onfocus', 'onblur', 'onmouseover', 'onerror', 'confirm', 'getParameter', 'getRequestURI', 'eval',
        'NULL', 'null', '@#$%^&*()', '@#￥%……&*（）——']
    disInputList = disInputList.concat(jsFilter).concat(htmlFilter).concat(urlFilter)
    const disfilter = []
    let i = 0
    if (data) {
        for (i = 0; i < data.length; i++) {
            disInputList.filter((item) => {
                if (item === data[i]) {
                    disfilter.push(item)
                } else if (item === data) {
                    disfilter.push(item)
                }
            })
        }
    }
    if (disfilter.length > 0) {
        status = false
        msg = '不允许输入' + disfilter[0] + '特殊字符'
        disfilter.forEach(item => {
            data = data.toString().replace(item, '')
        })
    }
    return [status, msg, data]
}