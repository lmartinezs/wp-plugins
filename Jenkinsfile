pipeline { 
    agent any 
    environment {
        BRANCH_NAME = "${GIT_BRANCH.split("/")[1]}"
    }
    stages {
        stage('Test'){
            steps {
               echo "TESTt"
            }
        }
        stage('Deploy') {
            steps {
                sh 'git status'
                script{                    
                    echo BRANCH_NAME
                    switch(BRANCH_NAME){
                        case "master":
                            echo "branch-master"
                            break;
                        case "staging":
                            echo "staging"
                            echo FTP_USER
                            echo FTP_PWD
                            break;
                        default:                            
                            break;
                        
                    }
                }
            }
        }
    }
}
