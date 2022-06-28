import { ReactChild } from 'react'
import { makeStyles } from '@mui/styles'
import { Theme } from '@mui/material/styles'
import {
  selectSidebarState,
  setSidebarState,
  setSidebarStateTimeout,
  useAppDispatch,
  useAppSelector,
} from '~/store'
import AppBarMenu from '~/components/molecules/layout/appBar/AppBar'
import CustomSidebar from '~/components/molecules/layout/CustomSidebar'
import IonIcon from '~/components/atoms/IonIcon/IonIcon'

/*
 * TODO: THIBO: Update AppBar
 * Custom Layout to implement blueprints
 * See: https://marmelab.com/react-admin/Theming.html#using-a-custom-layout
 */

/*
 * Use of mui makeStyles to create multiple styles reusing theme fm react-admin
 * see: https://mui.com/system/styles/basics/
 */
const useStyles = makeStyles((theme: Theme) => ({
  root: {
    display: 'flex',
    flexDirection: 'column',
    zIndex: 1,
    minHeight: '100vh',
    backgroundColor: theme.palette.background.default,
    position: 'relative',
  },
  appFrame: {
    display: 'flex',
    flexDirection: 'row',
    overflowX: 'auto',
    minHeight: '100vh',
  },
  contentWithAppbar: {
    display: 'flex',
    flexDirection: 'column',
    flexGrow: 1,
    width: 'auto',
    position: 'relative',
  },
  appbar: {
    display: 'flex',
    flexDirection: 'row',
    width: 'auto',
    position: 'unset',
  },
  content: {
    display: 'flex',
    flexDirection: 'column',
    flexGrow: 2,
    marginTop: theme.spacing(2),
    paddingLeft: theme.spacing(4),
    paddingRight: theme.spacing(4),
  },
  buttonCollapse: {
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    position: 'fixed',
    top: theme.spacing(6),
    background: theme.palette.background.paper,
    border: '1px solid #E2E6F3',
    boxSizing: 'border-box',
    borderRadius: theme.spacing(2),
    width: theme.spacing(4),
    height: theme.spacing(4),
    cursor: 'pointer',
    zIndex: '99',
  },
  rightBar: {
    borderRight: '1px solid #E2E6F3',
    boxSizing: 'unset',
    height: '100vh',
    overflowY: 'scroll',
    position: 'fixed',
    top: '0',
    scrollbarWidth: 'none',
    '&::-webkit-scrollbar': {
      width: 0,
    },
  },
  toClose: {
    left: 'calc(66px - 16px)',
    transition: 'left linear',
  },
  toOpen: {
    left: 'calc(279px - 16px)',
    transition: 'left linear',
  },
}))

interface IProps {
  children: ReactChild
}

/*
 * Component CustomLayout
 */
function CustomLayout({ children }: IProps) {
  const dispatch = useAppDispatch()
  const sidebarState = useAppSelector(selectSidebarState)

  const classes = useStyles()

  /*
   * Setup function to collapse sidebar when click on button
   */
  const collapseSidebar = () => {
    if (sidebarState) {
      setTimeout(() => {
        dispatch(setSidebarState(!sidebarState))
      }, 500)
    } else {
      dispatch(setSidebarState(!sidebarState))
    }
    dispatch(setSidebarStateTimeout(sidebarState))
  }

  return (
    <div className={classes.root}>
      <div className={classes.appFrame}>
        <CustomSidebar />
        <div
          className={classes.contentWithAppbar + ' ' + classes.rightBar}
          style={{
            left: sidebarState ? '279px' : '67px',
          }}
        >
          <button
            className={
              classes.buttonCollapse +
              ' ' +
              (!sidebarState ? classes.toClose : classes.toOpen)
            }
            onClick={collapseSidebar}
          >
            <IonIcon name="code-outline" style={{ width: 18, height: 18 }} />
          </button>
          <AppBarMenu />
          <div className={classes.content}>{children}</div>
        </div>
        {/*<Notification /> TODO: Set here Notification component*/}
      </div>
    </div>
  )
}

export default CustomLayout
