/**
 * 全局外链跳转确认（跨站警告）
 */
export function CrossSiteWarning() {
    const handleClick = (e) => {
        const link = e.target.closest('a')
        if (!link) return

        const href = link.getAttribute('href')
        const target = link.target || '_self'

        // 站内链接直接放行
        if (
            !href ||
            href.startsWith('#') ||
            href.startsWith('/') ||
            href.startsWith('./') ||
            href.startsWith('../')
        ) {
            return
        }

        try {
            const currentOrigin = window.location.origin
            const targetUrl = new URL(href)

            // 外部链接 → 弹窗确认
            if (targetUrl.origin !== currentOrigin) {
                e.preventDefault()

                const ok = confirm(`⚠️ 即将离开本站，跳转到外部网站：\n${href}\n是否继续？`)
                if (ok) {
                    window.open(href, target)
                }
            }
        } catch (err) {
            e.preventDefault()
        }
    }

    // 全局监听点击
    document.addEventListener('click', handleClick)
}