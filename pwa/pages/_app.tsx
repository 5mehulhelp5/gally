import { FunctionComponent, useEffect } from 'react'
import type { AppProps } from 'next/app'
import dynamic from 'next/dynamic'
import Head from 'next/head'
import Script from 'next/script'
import { appWithTranslation, useTranslation } from 'next-i18next'

import AppProvider from '~/components/AppProvider'
import nextI18nConfig from '~/next-i18next.config'
import { setLanguage } from '~/store'

import 'assets/scss/style.scss'

/*
 * Resolve for "Prop className did not match" between Server side and Client side
 * see solution here : https://github.com/vercel/next.js/issues/7322#issuecomment-1003545233
 */
const CustomLayoutWithNoSSR = dynamic(
  () => import('~/components/organisms/layout/CustomLayout'),
  { ssr: false }
) as FunctionComponent

function MyApp(props: AppProps) {
  const { pageProps } = props
  const Component = props.Component as FunctionComponent

  const { i18n } = useTranslation('common')
  useEffect(() => {
    if (i18n.language) {
      setLanguage(i18n.language)
    }
  }, [i18n.language])

  return (
    <>
      <Head>
        <title>Blink Admin</title>
      </Head>

      <AppProvider>
        <CustomLayoutWithNoSSR>
          <Component {...pageProps} />
        </CustomLayoutWithNoSSR>
      </AppProvider>
      <Script
        type="module"
        src="https://unpkg.com/ionicons@5.0.0/dist/ionicons/ionicons.esm.js"
      />
      <Script
        noModule
        src="https://unpkg.com/ionicons@5.0.0/dist/ionicons/ionicons.js"
      />
    </>
  )
}

// Only uncomment this method if you have blocking data requirements for
// every single page in your application. This disables the ability to
// perform automatic static optimization, causing every page in your app to
// be server-side rendered.
//
// MyApp.getInitialProps = async (appContext: AppContext) => {
//   // calls page's `getInitialProps` and fills `appProps.pageProps`
//   const appProps = await App.getInitialProps(appContext);

//   return { ...appProps }
// }

export default appWithTranslation(MyApp, nextI18nConfig)
